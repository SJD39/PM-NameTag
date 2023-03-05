<?php
namespace NameTag;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerDisplayNameChangeEvent;

// 实体生成事件
use pocketmine\event\entity\EntityDespawnEvent;
// 实体受伤事件
use pocketmine\event\entity\EntityDamageEvent;
// 实体重新获得健康事件
use pocketmine\event\entity\EntityRegainHealthEvent;
// 玩家重生事件
use pocketmine\event\player\PlayerRespawnEvent;
use Alias\Alias;

class NameTag extends PluginBase implements Listener
{
    private static $instance = null;

    public $configData = null;

    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLoad(): void
    {
        $this->getLogger()->info("NameTag 加载中！");
    }

    public function onEnable(): void
    {
        // 创建目录
        @mkdir($this->getDataFolder(), 0777, true);

        // 创建配置文件
        $this->config = new Config(
            $this->getDataFolder() . "config.yml",
            Config::YAML,
            array(
                "format" => "§a[/n][♥/h]"
            )
        );
        $this->config->save();

        $this->configData = $this->config->get("format");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("NameTag 已启用！");
    }

    public function onDisable(): void
    {
        $this->getLogger()->info("NameTag 已关闭！");
    }

    public function upNameTag($Alias, $health, $Entity)
    {
        $str = "";
        for ($i = 0; $i < strlen($this->configData); $i++) {
            if (substr($this->configData, $i, 1) == "/") {
                $temp = substr($this->configData, $i + 1, 1);
                if ($temp == "n") {
                    $str = $str . $Alias;
                } else if ($temp == "h") {
                    $str = $str . $health;
                } else if ($temp == "/") {
                    $str = $str . "/";
                }
                $i++;
            } else {
                $str = $str . substr($this->configData, $i, 1);
            }
        }
        $Entity->setNameTag($str);
    }

    // 玩家登录事件
    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $Entity = $event->getPlayer();
        $name = $Entity->getName();
        $Alias = Alias::getInstance()->getAlias($name);

        $this->upNameTag($Alias, $Entity->getHealth(), $Entity);
    }

    // 玩家重生事件
    public function PlayerRespawn(PlayerRespawnEvent $event)
    {
        $Entity = $event->getPlayer();
        $name = $Entity->getName();
        $Alias = Alias::getInstance()->getAlias($name);

        $this->upNameTag($Alias, $Entity->getMaxHealth(), $Entity);
    }

    // 实体受伤事件
    public function EntityDamage(EntityDamageEvent $event)
    {
        $Entity = $event->getEntity();

        if (strpos(get_class($Entity), 'player') == true) {
            $name = $Entity->getName();
            $Alias = Alias::getInstance()->getAlias($name);

            $this->upNameTag($Alias, $Entity->getHealth() - $event->getFinalDamage(), $Entity);
        } else {

        }
    }

    // 实体重新获得健康事件
    public function EntityRegainHealth(EntityRegainHealthEvent $event)
    {
        $Entity = $event->getEntity();

        if (strpos(get_class($Entity), 'player') == true) {
            $name = $Entity->getName();
            $Alias = Alias::getInstance()->getAlias($name);

            $this->upNameTag($Alias, $Entity->getHealth() + $event->getAmount(), $Entity);
        } else {

        }
    }

    // 玩家显示名称更改事件
    public function PlayerDisplayNameChange(PlayerDisplayNameChangeEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $Alias = Alias::getInstance()->getAlias($name);

        $this->upNameTag($Alias, $player->getHealth(), $player);
    }
}
?>