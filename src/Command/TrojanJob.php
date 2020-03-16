<?php

namespace App\Command;

use App\Models\{
    User,
    TrojanUser
};
use Exception;

class TrojanJob
{
    public static function CheckJob()
    {
        $users = User::all();
        foreach ($users as $user) {
            // 查找用户
            $TrojanUser = TrojanUser::where('user_id', $user->id)->first();
            if ($TrojanUser == null) {
                // 创建用户
                $TrojanUser           = new TrojanUser();
                $TrojanUser->user_id  = $user->id;
                $TrojanUser->password = hash('sha224', $user->passwd);
                if (!$TrojanUser->save()) {
                    echo ('创建用户失败！用户 ID：' . $user->id);
                    continue;
                }
            }

            // 上传统计
            $total_upload = (int) ($TrojanUser->upload - $TrojanUser->upload_old);
            if ($total_upload > 0) {
                $TrojanUser->addTraffic_u($total_upload);
            }

            // 下载统计
            $total_download = (int) ($TrojanUser->download - $TrojanUser->download_old);
            if ($total_download > 0) {
                $TrojanUser->addTraffic_d($total_download);
            }

            if ($total_upload > 0 || $total_download > 0) {
                // 添加流量记录
                $TrojanUser->addTraffic_log($total_upload, $total_download);
            }

            // 可用性检查
            if (
                $user->transfer_enable <= $user->u + $user->d
                ||
                $user->enable == 0
                ||
                (strtotime($user->expire_in) < time() && strtotime($user->expire_in) > 644447105)
            ) {
                // 流量耗尽、被封禁、账户过期
                $TrojanUser->quota = 0;
                $TrojanUser->save();
                continue;
            }

            // 重新分配流量
            switch ($_ENV['Trojan_allow_User_Type']) {
                case 1:
                    if ($user->class > 0) {
                        $TrojanUser->quota = $user->transfer_enable;
                    } else {
                        $TrojanUser->quota = 0;
                    }
                    break;
                case 2:
                    if (in_array($user->class, $_ENV['Trojan_allow_User_Class'])) {
                        $TrojanUser->quota = $user->transfer_enable;
                    } else {
                        $TrojanUser->quota = 0;
                    }
                    break;
                default:
                    $TrojanUser->quota = $user->transfer_enable;
                    break;
            }
            // 储存
            $TrojanUser->save();

            // 更新连接密码
            $TrojanUser->setPasswd();
        }
    }

    public static function DailyJob()
    {
        if (date('d') == '1') {
            try {
                TrojanUser::all()->update(['total' => 0]);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
