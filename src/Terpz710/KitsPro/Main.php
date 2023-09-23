<?php

namespace Terpz710\KitsPro;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\Config;

class KitsPro extends PluginBase implements Listener {

    /** @var FormAPI|null */
    private $formAPI;

    /** @var Config */
    private $kitsConfig;

    public function onEnable() {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);

    $kitCommand = new KitCommand($this);
    $this->getServer()->getCommandMap()->register("kit", $kitCommand);

    $this->formAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
    if ($this->formAPI === null) {
        $this->getLogger()->error("FormAPI is required for KitsPro.");
        $this->getServer()->getPluginManager()->disablePlugin($this);
        return;
    }

    $this->kitsConfig = new Config($this->getDataFolder() . "kits.yml", Config::YAML);
    $this->saveResource("kits.yml", false);
}

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "kit" && $sender instanceof Player) {
            $this->openKitUI($sender);
            return true;
        }
        return false;
    }

    public function openKitUI(Player $player) {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            $kitIndex = $data + 1;
            $kitName = "kit$kitIndex";
            $kitData = $this->kitsConfig->get("kits.$kitName");
            if ($kitData !== null) {
                $kitName = $kitData["name"];
                $kitItems = $kitData["items"];
                $this->sendConfirmationUI($player, $kitName);
            }
        });

        $form->setTitle("KitsPro Kit Selection");
        $form->setContent("Select a kit:");
        foreach ($this->kitsConfig->getAll()["kits"] as $kitName => $kitData) {
            $form->addButton($kitData["name"]);

        $player->sendForm($form);
    }

    public function sendConfirmationUI(Player $player, $kitName) {
        $form = new SimpleForm(function (Player $player, $data) use ($kitName) {
            if ($data === 0) {
                $player->sendMessage("You have confirmed Kit $kitName");
            }
        });

        $form->setTitle("KitsPro Kit Confirmation");
        $form->setContent("You've selected $kitName. Confirm?");

        $form->addButton("Confirm");
        $form->addButton("Cancel");

        $player->sendForm($form);
        }
    }
}
