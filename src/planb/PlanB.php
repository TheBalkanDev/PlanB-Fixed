<?php

namespace planb;

use planb\command\PlanBCommand;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class PlanB extends PluginBase{
    /** @var Config */
    private $backups;
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->saveResource("values.txt");
        @mkdir($this->getDataFolder());
        $this->backups = new Config($this->getDataFolder()."backups.txt", Config::ENUM);
        $this->getServer()->getCommandMap()->register("planb", new PlanBCommand($this));
    }
    /**
     * @param string $player
     * @return bool
     */
    public function isBackupPlayer($player){
        return $this->backups->exists(strtolower($player), true);
    }
    /** 
     * @param string $player 
     */
    public function addBackup($player){
        $this->backups->set(strtolower($player));
        $this->backups->save();
    }
    /** 
     * @param string $player 
     */
    public function removeBackup($player){
        $this->backups->remove(strtolower($player));
        $this->backups->save();
    }
    /** 
     * @param CommandSender $sender 
     */
    public function sendBackups(CommandSender $sender){
        $backupCount = 0;
        $backupNames = "";
        foreach(file($this->getDataFolder()."backups.txt", FILE_SKIP_EMPTY_LINES) as $name){
            $backupNames .= trim($name).", ";
            $backupCount++;
        }
        $sender->sendMessage(TextFormat::AQUA."Found ".$backupCount." backup player(s): ".substr($backupNames, 0, -2));
    }
    public function restoreOps(){
        foreach($this->getServer()->getOnlinePlayers() as $player){
            if(!$this->isBackupPlayer($player->getName()) and $player->isOp()){
                $player->setOp(false);
                $player->kick($this->getConfig()->get("kickReason"), false);
                if($this->getConfig()->get("notifyAll")){
                    $this->getServer()->broadcastMessage($this->getFixedMessage($player, $this->getConfig()->get("notifyMessage")));
                }
            }
            if($this->isBackupPlayer($player->getName()) and !$player->isOp()){
                $player->setOp(true);
                $player->sendMessage($this->getFixedMessage($player, $this->getConfig()->get("restoreMessage")));
            }
        }
    }
    /**
     * @param Player $player
     * @param string $message
     * @return string
     */
    public function getFixedMessage(Player $player, $message){
        return str_replace(   
            [
                "{PLAYER_ADDRESS}",
                "{PLAYER_DISPLAY_NAME}",
                "{PLAYER_NAME}",
                "{PLAYER_PORT}"
            ], 
            [
                $player->getAddress(),
                $player->getDisplayName(),
                $player->getName(),
                $player->getPort()
            ], 
            $message
        );
    }
}
