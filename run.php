<?php
//       _                         __  _           _                                  _   
//  ___ (_) ___  __ _ ___ _   _   / / | |_   _  __| | __ _  ___ _ __ ___   ___  _ __ | |_ 
// / _ \| |/ _ \/ _` / __| | | | / /  | | | | |/ _` |/ _` |/ _ \ '_ ` _ \ / _ \| '_ \| __|
//| (_) | |  __/ (_| \__ \ |_| |/ / |_| | |_| | (_| | (_| |  __/ | | | | |  __/| | | | |_ 
// \___/|_|\___|\__,_|___/\__, /_/ \___/ \__,_|\__,_|\__, |\___|_| |_| |_|\___||_| |_|\__|
//                        |___/                      |___/ 


/** 功能集 **/

//读取stdin
function ask($msg=""){
  echo $msg;
  return trim(fgets(STDIN));
}
//读取文件
function read_file($file_name){
  $file = fopen($file_name, "r") or die("Unable to open file!");
  $contant = fread($file,filesize($file_name));
  fclose($file);
  return $contant;
}
//写文件
function write_file($file_name, $content){
  $file = fopen($file_name, "w") or die("Unable to open file!");
  fwrite($file, $content);
  fclose($file);
  return true;
}

/** 主程序 - 检测 **/
if(@$_SERVER['REMOTE_ADDR'] !== null){
  http_response_code(400);
  exit("Http request in not supported!");
}
if(php_uname('s') !== 'Linux'){
  exit("Your system is not supported!");
}
if(exec("g++ -v 2>&1", $output) && count($output) < 3){
  exit("G++ is required.");
}
if(exec("git 2>&1", $output) && count($output) < 3){
  exit("Git is required.");
}
unset($output);
if(exec("cpulimit 2>&1", $output) && count($output) < 3){
  echo "Info: cpulimit is not installed, trying to install... \n";
  system("sudo apt-get update && sudo apt-get install cpulimit");
}
unset($output);

$version = json_decode(read_file("version.json"))->version;

/** 问候 **/
$oiez = "
       _                         __  _           _                                  _   
  ___ (_) ___  __ _ ___ _   _   / / | |_   _  __| | __ _  ___ _ __ ___   ___  _ __ | |_ 
 / _ \| |/ _ \/ _` / __| | | | / /  | | | | |/ _` |/ _` |/ _ \ '_ ` _ \ / _ \| '_ \| __|
| (_) | |  __/ (_| \__ \ |_| |/ / |_| | |_| | (_| | (_| |  __/ | | | | |  __/| | | | |_ 
 \___/|_|\___|\__,_|___/\__, /_/ \___/ \__,_|\__,_|\__, |\___|_| |_| |_|\___||_| |_|\__|
                        |___/                      |___/ 
";
echo $oiez;
echo "OIEJ ".$version." Alpha \nCopyright © 2019 OIEZ Team \n";

/** 检查更新 **/
echo "Checking updates... \n";
$latest_ver = json_decode(file_get_contents("https://raw.githubusercontent.com/oieasy/Judgement/master/version.json"))->version;
if(version_compare($version, $latest_ver, '<')){
  echo "Info: New version found: $latest_ver , Updating... \n";
  system("git pull");
}else{
  echo "Your version is up to date. \n";
}

if(!file_exists('./conf.php')){
  //没有配置文件，视为安装
  fopen("./conf.php", "w") or die("Error: Failed to write `./conf.php`");
  echo "Welcome to OIEJ! \n\n";
  echo "Installing... \n";
  $port = (int)ask("Http port (1919) :");
  $conf = [
      'listen_port' => $port ? $port : 1919,
      'Jid'         => md5(md5(mt_rand())+mt_rand()),
      'Key'         => hash('sha256', md5(mt_rand())),
      'strict_mode' => ask("Would you like to turn on strict mode? (Y/n):")=="y" or "Y" or "" ? true : false
  ];
  $conf_file = read_file("conf.example.php");
  $conf_file = str_replace("1919", $conf['listen_port'], $conf_file);
  $conf_file = str_replace('"strict_mode"    => false', '"strict_mode"    => ' . $conf['strict_mode'], $conf_file);
  $conf_file = str_replace('0847e7f350eb6249280d70da9a78946e', $conf['Jid'], $conf_file);
  $conf_file = str_replace('6ac3c336e4094835293a3fed8a4b5fedde1b5e2626d9838fed50693bba00af0e', $conf['Key'], $conf_file);
  $conf_file = str_replace("{time}", date('Y-m-d H:i:s'), $conf_file);
  write_file("./conf.php", $conf_file);
}else{
  //正常运行，读取配置
  $conf = require("conf.php");
  echo "Starting http server... Listening on 0.0.0.0:{$conf['listen_port']}\n";
  system("cd web/ && php -S 0.0.0.0:{$conf['listen_port']}");
}