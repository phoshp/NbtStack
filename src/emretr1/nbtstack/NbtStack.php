<?php

namespace emretr1\nbtstack;

use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;

class NbtStack extends PluginBase{

	/** @var CompoundTag[] */
	protected static $stackedNbts = [];

	/** @var string */
	protected static $dataPath;

	/**
	 * @return CompoundTag[]
	 */
	public static function getStackedNbts() : array{
		return self::$stackedNbts;
	}

	protected function onLoad(){
		self::$dataPath = $this->getDataFolder();
	}

	protected function onEnable(){
		$this->reloadConfig();
	}

	/**
	 * @param string $name
	 * @param bool   $create
	 *
	 * @return null|CompoundTag
	 */
	public static function getNbt(string $name, bool $create = true) : ?CompoundTag{
		if($create and !file_exists($path = self::$dataPath . $name)){
                        touch($path);
			self::$stackedNbts[$name] = new CompoundTag($name);
		}elseif(!isset(self::$stackedNbts[$name]) and file_exists(self::$dataPath . $name)){
			$stream = new BigEndianNBTStream;
			self::$stackedNbts[$name] = $stream->readCompressed(file_get_contents(self::$dataPath . $name));
		}
		return self::$stackedNbts[$name] ?? null;
	}
	
	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function containsNbt(string $name) : bool{
		return isset(self::$stackedNbts[$name]) ? true : file_exists(self::$dataPath . $name);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function deleteNbt(string $name) : bool{
		if(file_exists($path = self::$dataPath . $name)){
			unlink($path);

			return true;
		}
		return false;
	}

	protected function onDisable(){
		$stream = new BigEndianNBTStream();

		if($this->getConfig()->get("remove-not-used-nbt", false) === true){
			foreach((new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::$dataPath))) as $file){
				unlink($file);
			}
		}

		foreach(self::$stackedNbts as $name => $nbt){
			file_put_contents(self::$dataPath . $name, $stream->writeCompressed($nbt));
		}
	}
}
