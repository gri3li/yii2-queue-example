<?php

namespace app\controllers;

use app\job\ProcessJob;
use app\models\Request;
use Yii;
use yii\db\Query;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

class RequestController extends Controller
{
    public function behaviors(): array
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'handle' => ['GET'],
                ],
            ],
        ];
    }

    public function actionCreate(): array
    {
        $model = new Request();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);

            return [
                'result' => true,
                'id' => $model->id,
            ];
        }
        Yii::$app->response->setStatusCode(400);

        return [
            'result' => false,
        ];
    }

    public function actionHandle(int $delay): array
    {
        $query = (new Query)->from(Request::tableName())->where(['status' => null]);
        foreach ($query->each() as $request) {
            $transaction = Yii::$app->db->beginTransaction();
            $affectedRows = Request::updateAll(
                ['status' => Request::STATUS_IN_PROCESS],
                ['id' => $request['id'], 'status' => null]
            );
            if ($affectedRows) {
                try {
                    Yii::$app->queue->push(
                        new ProcessJob([
                            'delay' => $delay,
                            'requestId' => $request['id'],
                        ])
                    );
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
            $transaction->commit();
        }

        return [
            'result' => true,
        ];
    }
}
