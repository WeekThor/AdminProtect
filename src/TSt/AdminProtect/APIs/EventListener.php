<?php
namespace TSt\AdminProtect\APIs;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use TSt\AdminProtect\Loader;

class EventListener implements Listener{
    private $plugin;
    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * Display ban duration and ban reason for banned player when he tries to join
     * 
     * 
     * @priority HIGHEST
     */
    public function onPreLogin(PlayerPreLoginEvent $e):void{
        $name = mb_strtolower($e->getPlayerInfo()->getUsername(), "UTF-8");
        $banEntry = $this->plugin->getServer()->getNameBans()->getEntry($name);
        $msg = ["BannedPlayerKickMessage", "TempBannedPlayerKickMessage"];
        if($banEntry === null){
            $banEntry = $this->plugin->getServer()->getIPBans()->getEntry($e->getIp()); 
            $msg = ["BannedIPKickMessage", "TempBannedIPKickMessage"];
        }
        if($banEntry !== null){
            $banUntil = ($banEntry->getExpires() === null) ? null : $banEntry->getExpires()->getTimestamp();
            if($banUntil !== null){
                $msg = $this->plugin->getConfig()->get($msg[1]);
                $msg = str_replace('%sender%', $banEntry->getSource(), $msg);
                $msg = str_replace('%reason%', $banEntry->getReason(), $msg);
                $msg = str_replace('%duration%', date("d.m.Y H:i:s O", $banUntil), $msg);
            }else{
                $msg = $this->plugin->getConfig()->get($msg[0]);
                $msg = str_replace('%sender%', $banEntry->getSource(), $msg);
                $msg = str_replace('%reason%', $banEntry->getReason(), $msg);
            }
            $e->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_BANNED, $msg);
        }
    }
}