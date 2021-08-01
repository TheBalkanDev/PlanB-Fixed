<?php

namespace planb\command;

use planb\PlanB;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\command\ConsoleCommandSender;

class PlanBCommand extends Command{
    /** @var PlanB */
    private $plugin;
    /**
     * @param PlanB $plugin
     */
    public function __construct(PlanB $plugin){
        parent::__construct("planb", "Shows all PlanB commands", null, ["pb"]);
        $this->setPermission("planb.command.planb");
        $this->plugin = $plugin;
    }
    /** 
     * @param CommandSender $sender 
     */
    private function sendCommandHelp(CommandSender $sender){
        $commands = [
            "addbackup" => "Adds a player to backups.txt",
            "delbackup" => "Removes a player from backups.txt",
            "help" => "Shows all PlanB commands",
            "list" => "Lists all backup players",
            "restore" => "Restores OP status of all online players listed in backups.txt"
        ];
        $sender->sendMessage("PlanB commands:");
        foreach($commands as $name => $description){
            $sender->sendMessage("/planb $name: $description");
        }
    }
    /**
     * @param CommandSender $sender
     * @param string $label
     * @param string[] $args
     * @return bool
     */
    public function execute(CommandSender $sender, $label, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }
        if(isset($args[0])){
            switch(strtolower($args[0])){
                case "a":
                case "addbackup":
                    if(isset($args[1])){
                        if($sender instanceof ConsoleCommandSender){
                            if($this->plugin->isBackupPlayer($args[1])){
                                $sender->sendMessage(TextFormat::RED."$args[1] already exists in backups.txt.");
                            }
                            else{
                                $this->plugin->addBackup($args[1]);
                                $sender->sendMessage(TextFormat::GREEN."Added $args[1] to backups.txt.");
                            }
                        }
                        else{
                            $sender->sendMessage(TextFormat::RED."Please run this command on the console.");
                        }
                    }
                    else{
                        $sender->sendMessage(TextFormat::RED."Please specify a valid player."); 
                    }
                    break;
                case "d":
                case "delbackup":
                    if(isset($args[1])){
                        if($sender instanceof ConsoleCommandSender){
                            if($this->plugin->isBackupPlayer($args[1])){
                                $this->plugin->removeBackup($args[1]);
                                $sender->sendMessage(TextFormat::GREEN."Removed $args[1] from backups.txt.");
                            }
                            else{
                                $sender->sendMessage(TextFormat::RED."$args[1] does not exist in backups.txt.");
                            }
                        }
                        else{
                            $sender->sendMessage(TextFormat::RED."Please run this command on the console.");
                        }
                    }
                    else{
                        $sender->sendMessage(TextFormat::RED."Please specify a valid player.");
                    }
                    break;
                case "help":
                    $this->sendCommandHelp($sender);
                    break;
                case "l":
                case "list":
                    $this->plugin->sendBackups($sender);
                    break;
                case "r":
                case "restore":
                    if($this->plugin->isBackupPlayer($sender->getName()) or $sender instanceof ConsoleCommandSender){
                        $this->plugin->restoreOps();
                        $sender->sendMessage(TextFormat::YELLOW."Restoring the statuses of OPs...");
                    }
                    else{
                        $sender->sendMessage($this->plugin->getConfig()->get("noPermissionMessage"));
                    }
                    break;
                default:
                    $sender->sendMessage("Usage: /planb <sub-command> [parameters]");
                    break;
            }
        }
        else{
            $this->sendCommandHelp($sender);
            return false;
        }
        return true;
    }
}
