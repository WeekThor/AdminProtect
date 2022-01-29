<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;

class BanInfoC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "baninfo", "Shows player's active ban information", "/baninfo <player|address>", null, ["bi"]);
        $this->setPermission("adminprotect.baninfo.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /baninfo <{$this->cfg->get("Player")}|{$this->cfg->get("IP")}>");
            return false;
        }
        
        /**
         * @var BanEntry $bannedPlayer
         */
        $bannedPlayer = $this->getPlugin()->getServer()->getNameBans()->getEntry($args[0]);
        $msg = "";
        if($bannedPlayer === null){
            $msg = "§4[AdminProtect] §c{$this->cfg->get("PlayerNotBanned")}";
            $bannedPlayer = $this->getPlugin()->getServer()->getIPBans()->getEntry($args[0]);
            if($bannedPlayer === null){
                if($this->getPlugin()->isIPValid($args[0])){
                    $msg = "§4[AdminProtect] §c{$this->cfg->get("IPNotBanned")}";
                }
                $sender->sendMessage($msg);
                return false;
            }
        }
        $adminName = $bannedPlayer->getSource();
        $aPlayer = $sender->getServer()->getPlayerExact($bannedPlayer->getSource());
        if($aPlayer instanceof Player){
            $adminName = $aPlayer->getNameTag();
        }
        $until = ($bannedPlayer->getExpires() === null) ? $this->cfg->get("Forever") : $bannedPlayer->getExpires()->format("d.m.Y H:i");
        $canUnban = $this->cfg->get("mTrue");
        if(($sender instanceof Player and $sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))) or ($sender->hasPermission("adminprotect.unban.except.*") && $bannedPlayer->getSource() != $sender->getName())){
            $canUnban = $this->cfg->get("mFalse");
        }
        
        $msg = implode("\n", $this->cfg->get("BanInfo"));
        $msg = str_replace(["%player%", "%sender%", "%duration%", "%ban_date%", "%can_unban%", "%reason%"], [$args[0], $adminName, $until, $bannedPlayer->getCreated()->format("d.m.Y H:i"), $canUnban, $bannedPlayer->getReason()], $msg);
        $sender->sendMessage($msg);
        return true;
    }
}