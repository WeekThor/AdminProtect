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
     * it was to work, but doesn't...
     * Now it only shows in console player nick when player witch not banned joined to server, 
     * but don't show banned player nick when he connecting....
     * 
     * @priority HIGHEST
     */
    public function onPreLogin(PlayerPreLoginEvent $e):void{
        $name = mb_strtolower($e->getPlayerInfo()->getUsername(), "UTF-8");
        $banEntry = $this->plugin->getServer()->getNameBans()->getEntry($name);
        $this->plugin->getLogger()->info($name);
        if($banEntry !== null){
            $banUntil = ($banEntry->getExpires() === null) ? null : $banEntry->getExpires()->getTimestamp();
            if($banUntil !== null){
                $msg = $this->plugin->getConfig()->get("TempBannedPlayerKickMessage");
                $msg = str_replace('%sender%', $banEntry->getSource(), $msg);
                $msg = str_replace('%reason%', $banEntry->getReason(), $msg);
                $msg = str_replace('%duration%', date("d.m.Y H:i:s", $banUntil), $msg);
            }else{
                $msg = $this->plugin->getConfig()->get("BannedPlayerKickMessage");
                $msg = str_replace('%sender%', $banEntry->getSource(), $msg);
                $msg = str_replace('%reason%', $banEntry->getReason(), $msg);
            }
            $e->setKickReason(PlayerPreLoginEvent::KICK_REASON_BANNED, $msg);
        }
    }
}