<?php
/**
 * Configuration file
 * DB Credential Configuration
 * Redis Configuration
 * Email Configuration
 */

define("APP_ENV","local");
define("APP_URL","http://localhost"); 
/*
* Database connection
*/ 
define("DB_CONNECTION","mysql");
define("DB_HOST","127.0.0.1");
define("DB_PORT","3306");
define("DB_DATABASE","channel_fight");
define("DB_USERNAME","root");
define("DB_PASSWORD","root");

define("DB_ROUTER_READ","192.168.1.172");
define("DB_ROUTER_READ_PORT","7001");
define("DB_ROUTER_READ_WRITE","192.168.1.172");
define("DB_ROUTER_READ_WRITE_PORT","7002"); 


/*Redis configuration*/
define("REDIS_DB",0);
define("REDIS_HOST","192.168.1.169");
define("REDIS_PASSWORD","");
define("REDIS_PORT","6379");

define("REDIS_SCHEME","tcp");
define("REDIS_CACHE_DB",1);
define("REDIS_HOST1","6379");
define("REDIS_HOST2","6379");
define("REDIS_MASTER_PORT","6379");
define("REDIS_SLAVE_PORT_1","6379");
define("REDIS_SLAVE_PORT_2","6379");
 
/*Mail configuration*/
define("FROM_EMAIL_ADDRESS","inf@channelfight.com");
define("FROM_NAME","Channel Fight"); 
define("MAIL_DRIVER","smtp");
define("MAIL_HOST","smtp.sendgrid.net");
define("MAIL_PORT","587");
define("MAIL_USERNAME","devmd.jet@gmail.com");
define("MAIL_PASSWORD","jet@2019!");
define("MAIL_ENCRYPTION","tls");
define("MAIL_FROM_NAME","John Smith");
define("MAIL_FROM_ADDRESS","from@example.com");
define("SENDGRID_API_KEY","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");


/*Pusher configuration*/ 
define("PUSHER_APP_ID","");
define("PUSHER_APP_KEY","");
define("PUSHER_APP_SECRET","");
define("PUSHER_APP_CLUSTER","mt1");

define("MIX_PUSHER_APP_KEY","");
define("MIX_PUSHER_APP_CLUSTER","");

#Facebook App Credentials
define("FACEBOOK_APP_ID","2175020472763670");
define("FACEBOOK_APP_SECRET","a8930a9584422c46b3d1e5bf231af75d");
define("FACEBOOK_DEFAULT_GRAPH_VERSION","v3.2");
define("FACEBOOK_REDIRECT_URI","");

#Publicam Platform
define("PLATFORM_API_BASE_URL","http://114.143.181.228/publicam_platform");
define("JWT_TOKEN_EXPIRY_TIME","1440");

#Ip2Location
define("IP2LOCATION_URL","http://icon.api.publicam.in/v7/ip2location/getLocation");

#SuperStoreId=372
define("CONSUMER_KEY","SJFgJrokNA4PGLuqoUgfeWkCxOmeNFjo");
define("SHARED_SECRET_KEY","KFrgmkXurFqPLTIVfWOTvMhXKeHDLIRq1sb62X0KpyD2jq4f");

#Api SecuConfig
define("HMAC_HASH_KEY",'012345678'); #Do not change this value (Android SO Dependent)");
define("HMAC_HASH_ALGO",'SHA256'); #Do not change this value (Android SO Dependent)");
define("CIPHER_ALGO",'AES-256-CBC');  #Open SSL Enc/Dec Algo");

#Payload Enc/Dec Flag
define("DECRYPT_API_PAYLOAD",0);

#JWT SECRET KEY
define("SHA_KEY",'123456789');
define("TOKEN_EXPIRY",'1440');
