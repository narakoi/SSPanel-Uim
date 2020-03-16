<?php

namespace App\Models;

use App\Utils\Tools;

class TrojanUser extends Model
{
    protected $connection = 'default';

    protected $table = 'users';

    /**
     * User
     *
     * @return User
     */
    public function User()
    {
        return User::find($this->user_id);
    }

    /**
     * 更新连接密码
     *
     * @return void
     */
    public function setPasswd()
    {
        $this->password = hash('sha224', $this->User()->passwd);
        $this->save();
    }

    /**
     * 添加流量记录
     *
     * @return void
     */
    public function addTraffic_log($u, $d)
    {
        $node = Node::where('id', $_ENV['Trojan_bear_node'])->where('sort', 30)->first();
        if ($node != null) {
            $traffic            = new TrafficLog();
            $traffic->user_id   = $this->User()->id;
            $traffic->u         = $u;
            $traffic->d         = $d;
            $traffic->node_id   = $node->id;
            $traffic->rate      = $_ENV['Trojan_node_Traffic_Rate'];
            $traffic->traffic   = Tools::flowAutoShow(($u + $d) * $_ENV['Trojan_node_Traffic_Rate']);
            $traffic->log_time  = time();
            $traffic->save();
        }
    }

    /**
     * 核算上传流量
     *
     * @return void
     */
    public function addTraffic_u($u)
    {
        $user     = $this->User();
        $user->u += $u * $_ENV['Trojan_node_Traffic_Rate'];
        $user->save();

        $this->total      += $u * $_ENV['Trojan_node_Traffic_Rate'];
        $this->upload_old  = $this->upload;
        $this->save();
    }

    /**
     * 核算下载流量
     *
     * @return void
     */
    public function addTraffic_d($d)
    {
        $user     = $this->User();
        $user->d += $d * $_ENV['Trojan_node_Traffic_Rate'];
        $user->save();

        $this->total        += $d * $_ENV['Trojan_node_Traffic_Rate'];
        $this->download_old  = $this->download;
        $this->save();
    }

    /**
     * 重置流量数据
     *
     * @return void
     */
    public function reset()
    {
        $this->quota        = 0;
        $this->upload       = 0;
        $this->download     = 0;
        $this->upload_old   = 0;
        $this->download_old = 0;
        $this->save();
    }
}
