<?php

namespace Terpz710\KitsPro;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class KitCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("kit", "Access the kit GUI", "/kit");
        $this->setPermission("kitspro.kit");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermission($sender)) {
                $this->plugin->openKitGUI($sender);
            } else {
                $sender->sendMessage("You don't have permission to use this command.");
            }
        } else {
            $sender->sendMessage("This command can only be used by players.");
        }
        return true;
    }
}
