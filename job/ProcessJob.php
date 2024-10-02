<?php

namespace app\job;

use app\models\Request;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ProcessJob extends BaseObject implements JobInterface
{
    public int $delay;
    public int $requestId;

    public function execute($queue)
    {
        sleep($this->delay);
        $model = Request::findOne(['id' => $this->requestId, 'status' => Request::STATUS_IN_PROCESS]);
        $isApproved = rand(1, 100) <= 10;
        if ($isApproved) {
            $sql = "
                WITH check_approved AS (
                    SELECT COUNT(*) AS count
                    FROM requests
                    WHERE user_id = :user_id AND status = :status_approved
                )
                UPDATE requests
                SET status = CASE
                    WHEN (SELECT count FROM check_approved) = 0 THEN :status_approved
                    ELSE :status_declined
                END
                WHERE id = :request_id
            ";
            \Yii::$app->db->createCommand($sql)
                ->bindValue(':user_id', $model->user_id)
                ->bindValue(':request_id', $this->requestId)
                ->bindValue(':status_approved', Request::STATUS_APPROVED)
                ->bindValue(':status_declined', Request::STATUS_DECLINED)
                ->execute();
        } else {
            $sql = "
                UPDATE requests
                SET status = :status_declined
                WHERE id = :request_id
            ";
            $count = \Yii::$app->db->createCommand($sql)
                ->bindValue(':request_id', $this->requestId)
                ->bindValue(':status_declined', Request::STATUS_DECLINED)
                ->execute();
            var_dump($count);
        }
    }
}
