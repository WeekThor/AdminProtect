<?php
namespace TSt\AdminProtect\APIs;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use TSt\AdminProtect\Loader;

class EventListener implements Listener{
    private $plugin;
    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        $this->plugin->getLogger()->info("Registered");
    }
    
    /*
     * it's don't work when banned player tries to join
     * but it works if player not banned..................
     */
    public function onPreLogin(PlayerPreLoginEvent $e){
        $name = mb_strtolower($e->getPlayerInfo()->getUsername(), "UTF-8");
        $banEntry = $this->plugin->getServer()->getNameBans()->getEntry($name);
        $this->plugin->getLogger()->info($name);
        if($banEntry !== null){
            $banUntil = ($banEntry->getExpires() === null) ? null : $banEntry->getExpires()->getTimestamp();
            if($banUntil !== null){
                $msg = $this->getConfig()->get("TempBannedPlayerKickMessage");
                $msg = str_replace('%sender%', $banEntry->getSource(), $msg);
                $msg = str_replace('%reason%', $banEntry->getReason(), $msg);
                $msg = str_replace('%duration%', date("d.m.Y H:i:s", $banUntil), $msg);
            }else{
                $msg = $this->getConfig()->get("BannedPlayerKickMessage");
                $msg = str_replace('%sender%', $banEntry->getSource(), $msg);
                $msg = str_replace('%reason%', $banEntry->getReason(), $msg);
            }
            $e->setKickReason(PlayerPreLoginEvent::KICK_REASON_BANNED, $msg);
        }
    }
}