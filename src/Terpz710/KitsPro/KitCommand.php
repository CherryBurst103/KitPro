<?php

namespace Terpz710\KitsPro;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use Terpz710\KitsPro\Main;

class KitCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("kit", "Access kit selection", "/kit <kit_name>");
        $this->setPermission("kitspro.kit");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args) {
    if ($sender instanceof Player) {
        if (count($args) === 1) {
            $kitName = strtolower($args[0]);
            $this->plugin->openKitUI($sender, $kitName);
        } else {
            $sender->sendMessage("Usage: /kit <kit_name>");
        }
    } else {
        $sender->sendMessage("This command can only be used in-game.");
    }
    return true;
    }
}
