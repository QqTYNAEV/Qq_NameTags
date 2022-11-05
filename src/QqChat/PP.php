<?php

// VK -> @qq_tynaev
// GitHub -> QqTYNAEV

namespace QqChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\scheduler\CallbackTask;
use _64FF00\PurePerms\event\PPGroupChangedEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerJoinEvent;

class PP extends PluginBase implements Listener{
    
    
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->cln = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
        @mkdir($this->getDataFolder());
        $this->saveResource("settings.yml");
        $this->saveResource("config.yml");
        if($this->pp){
            $this->getLogger()->info("§a>>>§f Плагин успешно загружен");
        }else{
            $this->getLogger()->error("§c>>>§f Плагин §bPurePerms§f не найден!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $this->c = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $this->s = new Config($this->getDataFolder()."settings.yml", Config::YAML);
        $time = $this->s->get("update");
        if(!is_numeric($time)){
            $this->t = $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "tagUpdate")), 20);
        }else{
            if($time != 0){
                $this->t = $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "tagUpdate")), $time);
            }
        }
    }
    
    
    public function onJoin(PlayerJoinEvent $j_e){
        $p = $j_e->getPlayer();
        $pp = $this->pp->getUserDataMgr()->getGroup($p)->getName();
        $p->setNameTag($this->nameTag($p, $pp));
        $p->setDisplayName($this->displayName($p, $pp));
    }
    
    
    public function tagUpdate(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $pp = $this->pp->getUserDataMgr()->getGroup($p)->getName();
            $p->setNameTag($this->nameTag($p, $pp));
            $p->setDisplayName($this->displayName($p, $pp));
        }
    }
    
    
    public function onGG(PPGroupChangedEvent $pp_e){
        $p = $pp_e->getPlayer();
        if($this->getServer()->getPlayer($p->getName())){
            $pp = $pp_e->getGroup()->getName();
            $p->setNameTag($this->nameTag($p, $pp));
            $p->setDisplayName($this->displayName($p, $pp));
        }
    }
    
    
    public function nameTag($p, $pp){
        $n = $p->getName();
        $d = $p->getDeviceModel();
        $ping = $p->getPing();
        $hp = $p->getHealth();
        $item = $p->getItemInHand()->getId().":".$p->getItemInHand()->getDamage();
        if($this->cln){
            if($this->cln->getPlayerFaction($n) == null){
                $cl = $this->s->get("clan-none");
            }else{
                $cl = $this->cln->getPlayerFaction($n);
            }
        }else{
            $cl = "{clan}"; //По прикольнее слелать можно
        }
        switch($p->getGamemode()){
			case "0": $gm = "Выживание";
			break;
			case "1": $gm = "Творческий";
			break;
			case "2": $gm = "Приключение";
			break;
			case "3": $gm = "Наблюдатель";
			break;
		}
		switch($p->getDeviceOS()){
			case "1": $os = "Android";
			break;
			case "2": $os = "IOS";
			break;
			case "3": $os = "macOS";
			break;
			case "7": $os = "Windows 10"; //Я хз какой айди в винды 11
			break;
			case "8": $os = "Windows";
			break;
			case "10": $os = "PlayStation";
			break;
			default: $os = "Неизвестно";
		}
		if($this->c->exists($pp)){
			$n_tag = str_replace(["{name}", "{clan}", "{ping}", "{hp}", "{os}", "{device}", "{gm}", "{item}"], [$n, $cl, $ping, $hp, $os, $d, $gm, $item], $this->c->get($pp)["tag"]);
		}else{
		    $n_tag = $n;
		}
		return $n_tag;
    }
    
    
    public function displayName($p, $pp){
        $n = $p->getName();
        $d = $p->getDeviceModel();
        $ping = $p->getPing();
        $hp = $p->getHealth();
        $item = $p->getItemInHand()->getId().":".$p->getItemInHand()->getDamage();
        if($this->cln){
            if($this->cln->getPlayerFaction($n) == null){
                $cl = $this->s->get("clan-none");
            }else{
                $cl = $this->cln->getPlayerFaction($n);
            }
        }else{
            $cl = "{clan}"; //По прикольнее слелать можно
        }
        switch($p->getGamemode()){
			case "0": $gm = "Выживание";
			break;
			case "1": $gm = "Творческий";
			break;
			case "2": $gm = "Приключение";
			break;
			case "3": $gm = "Наблюдатель";
			break;
		}
		switch($p->getDeviceOS()){
			case "1": $os = "Android";
			break;
			case "2": $os = "IOS";
			break;
			case "3": $os = "macOS";
			break;
			case "7": $os = "Windows 10"; //Я хз какой айди в винды 11
			break;
			case "8": $os = "Windows";
			break;
			case "10": $os = "PlayStation";
			break;
			default: $os = "Неизвестно";
		}
		if($this->c->exists($pp)){
			$display = str_replace(["{name}", "{clan}", "{ping}", "{hp}", "{os}", "{device}", "{gm}", "{item}"], [$n, $cl, $ping, $hp, $os, $d, $gm, $item], $this->c->get($pp)["display"]);
		}else{
		    $display = $n;
		}
		return $display;
    }
    
    
    public function onChat(PlayerChatEvent $c_e){
        $p = $c_e->getPlayer();
        $pp = $this->pp->getUserDataMgr()->getGroup($p)->getName();
        $msg = $c_e->getMessage();
        $c_e->setFormat($this->msgTask($p, $msg, $pp)); //Использует функцию которая находится в 
    }
    
    
    public function msgTask($p, $msg, $pp){
        $n = $p->getName();
        $d = $p->getDeviceModel();
        $ping = $p->getPing();
        $hp = $p->getHealth();
        $item = $p->getItemInHand()->getId().":".$p->getItemInHand()->getDamage();
        if($this->s->get("antiColor-chat") == true){
            $m = $this->delCol($msg);
        }else{
            $m = $msg;
        }
        if($this->cln){
            if($this->cln->getPlayerFaction($n) == null){
                $cl = $this->s->get("clan-none");
            }else{
                $cl = $this->cln->getPlayerFaction($n);
            }
        }else{
            $cl = "{clan}"; //По прикольнее слелать можно
        }
        switch($p->getGamemode()){
			case "0": $gm = "Выживание";
			break;
			case "1": $gm = "Творческий";
			break;
			case "2": $gm = "Приключение";
			break;
			case "3": $gm = "Наблюдатель";
			break;
		}
		switch($p->getDeviceOS()){
			case "1": $os = "Android";
			break;
			case "2": $os = "IOS";
			break;
			case "3": $os = "macOS";
			break;
			case "7": $os = "Windows 10"; //Я хз какой айди в винды 11
			break;
			case "8": $os = "Windows";
			break;
			case "10": $os = "PlayStation";
			break;
			default: $os = "Неизвестно";
		}
		if($this->c->exists($pp)){
			$msg_succes = str_replace(["{name}", "{clan}", "{ping}", "{hp}", "{os}", "{device}", "{gm}", "{item}", "{msg}"], [$n, $cl, $ping, $hp, $os, $d, $gm, $item, $m], $this->c->get($pp)["chat"]);
        }else{
            $msg_succes = $n . " > " . $msg;
        }
        return $msg_succes;
    }
    
    
    public function delCol($msg){
        $msg = str_replace(TextFormat::BLACK, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::DARK_BLUE, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::DARK_GREEN, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::DARK_AQUA, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::DARK_RED, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::DARK_PURPLE, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::GOLD, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::GRAY, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::DARK_GRAY, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::BLUE, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::GREEN, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::AQUA, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::RED, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::LIGHT_PURPLE, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::YELLOW, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::OBFUSCATED, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::BOLD, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::ITALIC, TextFormat::RESET, $msg);
        $msg = str_replace(TextFormat::RESET, TextFormat::RESET, $msg);
        return $msg;
    }
    
}
