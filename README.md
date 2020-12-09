1.项目介绍

  	联盟链节点监控项目

2.项目功能

 	联盟链节点监控项目

3.项目部署环境（LNMP环境）

  	a. php7.2环境

  	b. mysql5.7以上

  	c. nginx环境安装   
  	
  	d. redis 如果有密码，需要修改common/config/main-local.php redis->password

4.部署要素

  	a.设置服务器路径到 /api/web 下 (vendor已包含，不用composer安装)

  	b.创建数据库
  	    表结构文件:console/migration/monitor.sql
  	    默认的数据库信息为：
    	库名 ； monitor
    	编码 ： utf8
    	排序 ： utf8_general_ci
  	    (可运行 php yii migrate 初始化表结构.注意：要先建好库，修改common/config/main-local.php里db信息)
  	    
  	
5.运行脚本(检查各项目节点是否正常) 	

    */5 * * * * php path_xxx/yii check/index >/dev/null

6.代码组织架构
	api
	    assets/              
	    components/          
	    config/              
	    controllers/         
	    models/
	    helps/
	    modules/             
	    runtime/ 
	    tests/
	    views/            
	    web/                 
	common       
	    config/
	    helps/                        
	    models/                         
	console
	    components/  
	    config/              
	    controllers/
	    migrations/           
	    models/              
	    runtime/             
	environments/            
    vagrant/
	vendor/    
	
	6. 服务器所需扩展
	所需扩展：
[PHP Modules]
calendar
Core
ctype
curl
date
exif
fileinfo
filter
ftp
gd
gettext
hash
iconv
json
libxml
mbstring
mysqli
mysqlnd
openssl
pcntl
pcre
PDO
pdo_mysql
Phar
posix
readline
Reflection
session
shmop
sockets
SPL
standard
sysvmsg
sysvsem
sysvshm
tokenizer
Zend OPcache
zlib

[Zend Modules]
