<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\API;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class UnbanC extends API{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "unban", "Unban nickname", "/unban <player>", null, ["tunban", "pardon"]);
        $this->setPermission("admin.protect.unban.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /unban <{$this->cfg->get("Player")}>");
        }else{
            
            if($sender instanceof Player){
                $admin = $sender->getNameTag();
            }else{
                $admin = $this->cfg->get("Console");
            }
            $name = array_shift($args);
            $allow_unban = true;
            if($this->getPlugin()->banInfoAPI != null){
                $banInfo = $this->getPlugin()->banInfoAPI->getBanInfo(false);
                if($banInfo->get($name) == null){
                    $allow_unban = false;
                }
            }
            if($allow_unban){
               
                $broadcast = $this->cfg->get("UnbanBroadcast");
                $broadcast = str_replace("%sender%", $admin, $broadcast);
                $broadcast = str_replace("%player%", $name, $broadcast);
                $sender->getServer()->getNameBans()->remove($name);
                $sender->getServer()->broadcastMessage($broadcast);
            }else{
                $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("PlayerNotBanned")}");
            }
        }
        return true;
    }
}