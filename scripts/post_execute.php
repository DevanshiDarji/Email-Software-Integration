<?php
/*********************************************************************************
 * This file is part of package Email Software Integartion.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Email Software Integartion is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
global $sugar_config;
$databaseType = $sugar_config['dbconfig']['db_type'];

if($databaseType == 'mysql'){
	$alterApiConfigurationTable = "SHOW TABLES LIKE 'vi_api_configuration'"; 
	$alterApiConfigurationTableResult = $GLOBALS['db']->query($alterApiConfigurationTable);

	if($alterApiConfigurationTableResult->num_rows > 0){
		
		$columnExist = "SHOW COLUMNS FROM vi_api_configuration LIKE 'plan_type'";
	    $columnExistResult = $GLOBALS['db']->query($columnExist);
	    if($columnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_api_configuration ADD plan_type TINYINT(1) NULL AFTER email_software";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		//Integration api_key table
		$integration = "CREATE TABLE IF NOT EXISTS vi_api_configuration(id CHAR(36) PRIMARY KEY,
													email_software VARCHAR(255),
													plan_type TINYINT(1),
												 	api_key VARCHAR(255),
												 	title VARCHAR(255),
												 	deleted TINYINT(1) DEFAULT 0)";
		$integrationResult = $GLOBALS['db']->query($integration);
	}//end of else

	$alterAutomaticSyncTable = "SHOW TABLES LIKE 'vi_automatic_sync'"; 
	$alterAutomaticSyncTableResult = $GLOBALS['db']->query($alterAutomaticSyncTable);

	if($alterAutomaticSyncTableResult->num_rows > 0){
		$autoSyncColumnExist = "SHOW COLUMNS FROM vi_automatic_sync LIKE 'auto_sync_ems'";
	    $autoSyncColumnExistResult = $GLOBALS['db']->query($autoSyncColumnExist);
	    if($autoSyncColumnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_automatic_sync ADD auto_sync_ems TINYINT( 1 ) NOT NULL";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if

	    $autoSyncEMSToSuiteColumnExist = "SHOW COLUMNS FROM vi_automatic_sync LIKE 'sync_ems_to_suite'";
	    $autoSyncEMSToSuiteColumnExistResult = $GLOBALS['db']->query($autoSyncEMSToSuiteColumnExist);
	    if($autoSyncEMSToSuiteColumnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE vi_automatic_sync ADD sync_ems_to_suite TINYINT( 1 ) NOT NULL DEFAULT 0";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		//Integration step-4 Automatic Sync Table
		$automaticSync = "CREATE TABLE IF NOT EXISTS vi_automatic_sync(vi_automatic_sync_id CHAR(36) PRIMARY KEY,
																	   	sync_software VARCHAR(255),
																	 	sel_mapping_module_list VARCHAR(255),
																	 	sync_to_es tinyint(4),
																		deleted TINYINT(1) DEFAULT 0,
																		auto_sync_ems TINYINT(1) NOT NULL,
																		sync_ems_to_suite TINYINT(1) NOT NULL DEFAULT 0)";
		$automaticSyncResult = $GLOBALS['db']->query($automaticSync);
	}//end of else

	$alterEmailFieldTable = "SHOW TABLES LIKE 'vi_email_fields'"; 
	$alterEmailFieldTableResult = $GLOBALS['db']->query($alterEmailFieldTable);

	if($alterEmailFieldTableResult->num_rows > 0){
		
		$alterValue = "ALTER TABLE vi_email_fields CHANGE fields fields longtext NULL DEFAULT NULL;";
		$alterValueResult = $GLOBALS['db']->query($alterValue);

		$moduleMapColumnExist = "SHOW COLUMNS FROM vi_email_fields LIKE 'module_map_id'";
	    $moduleMapColumnExistResult = $GLOBALS['db']->query($moduleMapColumnExist);
	    if($moduleMapColumnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_email_fields ADD module_map_id CHAR(36) NULL AFTER id";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		//Integration module sync fields table
		$allModuleSyncFields = "CREATE TABLE IF NOT EXISTS vi_email_fields(id CHAR(36) PRIMARY KEY,
													module_map_id CHAR(36),
													email_software VARCHAR(255),
												 	module VARCHAR(255),
												 	fields longtext)";
		$allModuleSyncFieldsResult = $GLOBALS['db']->query($allModuleSyncFields);
	}//end of else

	//Integration module contacts Field Mappings for target list module only
	$contactsFieldMappings = "CREATE TABLE IF NOT EXISTS vi_integration_contacts_field_mapping(
														contacts_field_mapping_id CHAR(36) PRIMARY KEY,
														module_mapping_id VARCHAR(255),
													 	suitecrm_contacts_module_fields VARCHAR(255),
													 	sendgrid_contacts_module_fields VARCHAR(255),
														deleted TINYINT(1) DEFAULT 0)";
	$contactsFieldMappingsResult = $GLOBALS['db']->query($contactsFieldMappings);

	//Integration module Field Mappings
	$fieldMappings = "CREATE TABLE IF NOT EXISTS vi_integration_field_mapping(field_mapping_id CHAR(36) PRIMARY KEY,
														module_mapping_id VARCHAR(255),
													 	suitecrm_module_fields VARCHAR(255),
													 	es_module_fields VARCHAR(255),
														deleted TINYINT(1) DEFAULT 0)";
	$fieldMappingsResult = $GLOBALS['db']->query($fieldMappings);

	$alterModuleMappingTable = "SHOW TABLES LIKE 'vi_module_mapping'"; 
	$alterModuleMappingTableResult = $GLOBALS['db']->query($alterModuleMappingTable);

	if($alterModuleMappingTableResult->num_rows > 0){
		$moduleMapColumnExist = "SHOW COLUMNS FROM vi_module_mapping LIKE 'batch_record'";
	    $moduleMapColumnExistResult = $GLOBALS['db']->query($moduleMapColumnExist);
	    if($moduleMapColumnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_module_mapping ADD batch_record INT( 11 ) NOT NULL AFTER deleted";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if

	    $moduleMapColumnExist = "SHOW COLUMNS FROM vi_module_mapping LIKE 'batch_management_status'";
	    $moduleMapColumnExistResult = $GLOBALS['db']->query($moduleMapColumnExist);
	    if($moduleMapColumnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_module_mapping ADD batch_management_status TINYINT( 1 ) NOT NULL AFTER batch_record";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if

	    $targetListSubpanelModuleColumnExist = "SHOW COLUMNS FROM vi_module_mapping LIKE 'target_list_subpanel_module'";
	    $targetListSubpanelModuleColumnExistResult = $GLOBALS['db']->query($targetListSubpanelModuleColumnExist);
	    if($targetListSubpanelModuleColumnExistResult->num_rows == 0){
	        $addTargetListSubpanelModuleColumn = "ALTER TABLE  vi_module_mapping ADD target_list_subpanel_module VARCHAR(255) NULL AFTER suitecrm_module";
	        $addTargetListSubpanelModuleColumnResult = $GLOBALS['db']->query($addTargetListSubpanelModuleColumn);
	    }//end of if

	    $conditionalOperatorColumnExist = "SHOW COLUMNS FROM vi_module_mapping LIKE 'conditional_operator'";
	    $conditionalOperatorColumnExistResult = $GLOBALS['db']->query($conditionalOperatorColumnExist);
	    if($conditionalOperatorColumnExistResult->num_rows == 0){
	        $addconditionalOperatorColumn = "ALTER TABLE  vi_module_mapping ADD conditional_operator VARCHAR( 5 ) NULL AFTER batch_management_status";
	        $addconditionalOperatorColumnResult = $GLOBALS['db']->query($addconditionalOperatorColumn);
	    }//end of if
	}else{
		//Integration Module Mappings
		$moduleMappings = "CREATE TABLE IF NOT EXISTS vi_module_mapping(module_mapping_id CHAR(36) PRIMARY KEY,
															title VARCHAR(255),
														 	suitecrm_module VARCHAR(255),
														 	target_list_subpanel_module VARCHAR(255),
														 	es_module VARCHAR(255),
														 	email_software VARCHAR(255),
														 	status VARCHAR(255),
														 	deleted TINYINT(1) DEFAULT 0,
														 	batch_record INT(11),
														 	batch_management_status TINYINT(1),
														 	conditional_operator VARCHAR(5))";
		$moduleMappingsResult = $GLOBALS['db']->query($moduleMappings);
	}//end of else

	//Integration step=3 synchronize
	$synchronize = "CREATE TABLE IF NOT EXISTS vi_synchronize(sync_id CHAR(36) PRIMARY KEY,
															sync_software VARCHAR(255),
														 	sel_mapping_module_list VARCHAR(255),
														 	deleted TINYINT(1) DEFAULT 0)";
	$synchronizeResult = $GLOBALS['db']->query($synchronize);

	//For Accounts Module
	$alterAccountsTable = "SHOW TABLES LIKE 'vi_accounts_es'"; 
	$alterAccountsTableResult = $GLOBALS['db']->query($alterAccountsTable);

	if($alterAccountsTableResult->num_rows > 0){
		$columnExist = "SHOW COLUMNS FROM vi_accounts_es LIKE 'deleted'";
	    $columnExistResult = $GLOBALS['db']->query($columnExist);
	    if($columnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_accounts_es ADD deleted TINYINT(1) DEFAULT 0";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		$esAccount = "CREATE TABLE IF NOT EXISTS vi_accounts_es(id CHAR(36) PRIMARY KEY,
							vi_suitecrm_account_id VARCHAR(255),
						 	vi_es_account_id VARCHAR(255),
						 	vi_es_name VARCHAR(255),
						 	deleted TINYINT(1) DEFAULT 0)";
		$esAccountResult = $GLOBALS['db']->query($esAccount);
	}//end of else

	//For Asset Module
	$alterAssetsTable = "SHOW TABLES LIKE 'vi_assets_es'"; 
	$alterAssetsTableResult = $GLOBALS['db']->query($alterAssetsTable);

	if($alterAssetsTableResult->num_rows > 0){
		$columnExist = "SHOW COLUMNS FROM vi_assets_es LIKE 'deleted'";
	    $columnExistResult = $GLOBALS['db']->query($columnExist);
	    if($columnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_assets_es ADD deleted TINYINT(1) DEFAULT 0";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		$esAsset = "CREATE TABLE IF NOT EXISTS vi_assets_es(id CHAR(36) PRIMARY KEY,
						vi_suitecrm_assets_id VARCHAR(255),
					 	vi_es_assets_id VARCHAR(255),
					 	vi_es_name VARCHAR(255),
					 	deleted TINYINT(1) DEFAULT 0)";
		$esAssetResult = $GLOBALS['db']->query($esAsset);
	}//end of else

	//For Campaigns Module
	$alterCampaignsTable = "SHOW TABLES LIKE 'vi_campaigns_es'"; 
	$alterCampaignsTableResult = $GLOBALS['db']->query($alterCampaignsTable);

	if($alterCampaignsTableResult->num_rows > 0){
		$columnExist = "SHOW COLUMNS FROM vi_campaigns_es LIKE 'deleted'";
	    $columnExistResult = $GLOBALS['db']->query($columnExist);
	    if($columnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_campaigns_es ADD deleted TINYINT(1) DEFAULT 0";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		$esCampaigns = "CREATE TABLE IF NOT EXISTS vi_campaigns_es(id CHAR(36) PRIMARY KEY,
								vi_suitecrm_campaigns_id VARCHAR(255),
							 	vi_es_campaign_id VARCHAR(255),
							 	vi_es_name VARCHAR(255),
							 	deleted TINYINT(1) DEFAULT 0)";
		$esCampaignsResult = $GLOBALS['db']->query($esCampaigns);
	}//end of else
	
	//For Contacts Module
	$alterContactsTable = "SHOW TABLES LIKE 'vi_contacts_es'"; 
	$alterContactsTableResult = $GLOBALS['db']->query($alterContactsTable);

	if($alterContactsTableResult->num_rows > 0){
		$columnExist = "SHOW COLUMNS FROM vi_contacts_es LIKE 'deleted'";
	    $columnExistResult = $GLOBALS['db']->query($columnExist);
	    if($columnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_contacts_es ADD deleted TINYINT(1) DEFAULT 0";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		$esContacts = "CREATE TABLE IF NOT EXISTS vi_contacts_es(id CHAR(36) PRIMARY KEY,
							vi_suitecrm_contact_id VARCHAR(255),
						 	vi_es_contact_id VARCHAR(255),
						 	vi_suitecrm_lead_id VARCHAR(255),
						 	vi_es_name VARCHAR(255),
						 	vi_es_list_id VARCHAR(255),
						 	vi_suitecrm_module VARCHAR(255),
						 	vi_es_lead_id VARCHAR(255),
						 	deleted TINYINT(1) DEFAULT 0)";
		$esContactsResult = $GLOBALS['db']->query($esContacts);
	}//end of else

	//For Segments Module
	$alterSegmentsTable = "SHOW TABLES LIKE 'vi_segments_es'"; 
	$alterSegmentsTableResult = $GLOBALS['db']->query($alterSegmentsTable);

	if($alterSegmentsTableResult->num_rows > 0){
		$columnExist = "SHOW COLUMNS FROM vi_segments_es LIKE 'deleted'";
	    $columnExistResult = $GLOBALS['db']->query($columnExist);
	    if($columnExistResult->num_rows == 0){
	        $addColumn = "ALTER TABLE  vi_segments_es ADD deleted TINYINT(1) DEFAULT 0";
	        $addColumnResult = $GLOBALS['db']->query($addColumn);
	    }//end of if
	}else{
		$esSegments = "CREATE TABLE IF NOT EXISTS vi_segments_es(id CHAR(36) PRIMARY KEY,
							vi_suitecrm_segments_id VARCHAR(255),
						 	vi_es_segments_id VARCHAR(255),
						 	vi_es_name VARCHAR(255),
						 	deleted TINYINT(1) DEFAULT 0)";
		$esSegmentsResult = $GLOBALS['db']->query($esSegments);
	}//end of else

	//ems schedule sync
	$emsScheduleSync = "CREATE TABLE IF NOT EXISTS vi_ems_schedule_sync(id INT(11) AUTO_INCREMENT PRIMARY KEY,
																	ems_software VARCHAR(255),
																 	mapping_id CHAR(36),
																 	batch_record INT(11),
																 	status TINYINT(1),
																 	start_date_time DATETIME,
																 	end_date_time DATETIME)";
	$emsScheduleSyncResult = $GLOBALS['db']->query($emsScheduleSync);

	//EMS Condition Table
    $emailSoftwareIntegrationCondition = "CREATE TABLE IF NOT EXISTS vi_ems_conditions(
                                                id CHAR(36) NOT NULL PRIMARY KEY,
                                                module_path VARCHAR(255) NULL,
                                                field VARCHAR(255) NULL,
                                                operator VARCHAR(255) NULL,
                                                value_type VARCHAR(255) NULL,
                                                value VARCHAR(255) NULL,
                                                module_mapping_id CHAR(36) NULL,
                                                condition_type VARCHAR(255) NULL,
                                                date_entered DATETIME NOT NULL,
                                                deleted TINYINT(1) NOT NULL DEFAULT '0'
                                                )";
    $emailSoftwareIntegrationConditionResult = $GLOBALS['db']->query($emailSoftwareIntegrationCondition);

}else if($databaseType == 'mssql'){

	$columnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_api_configuration'
                    AND COLUMN_NAME = 'plan_type')
                    BEGIN
                        ALTER TABLE vi_api_configuration
                        ADD plan_type SMALLINT NULL
                    END";
    $columnExistResult = $GLOBALS['db']->query($columnExist);

    //Integration api_key table
    $integration = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_api_configuration]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_api_configuration](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [email_software] [VARCHAR](255) NULL,
                                [plan_type] [SMALLINT] NULL,
                                [api_key] [VARCHAR](255) NULL,
                                [title] [VARCHAR](255) NULL,
                             	[deleted] [SMALLINT] NOT NULL DEFAULT 0
                                )
                                END";
    $integrationResult = $GLOBALS['db']->query($integration);

    //Integration step-4 Automatic Sync Table
	$automaticSync = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_automatic_sync]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_automatic_sync](
                                [vi_automatic_sync_id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [sync_software] [VARCHAR](255) NULL,
                                [sel_mapping_module_list] [VARCHAR](255) NULL,
                                [sync_to_es] [SMALLINT] NULL,
                             	[deleted] [SMALLINT] NOT NULL DEFAULT 0
                                )
                                END";
    $automaticSyncResult = $GLOBALS['db']->query($automaticSync);

    $alterValue = "IF EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_email_fields]') and OBJECTPROPERTY(id, N'IsTable') = 1)
					BEGIN
					ALTER TABLE [dbo].[vi_email_fields] 
					ALTER COLUMN [fields] [NVARCHAR](MAX) NULL
					END";
	$alterValueResult = $GLOBALS['db']->query($alterValue);

    $moduleMapColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_email_fields'
                    AND COLUMN_NAME = 'module_map_id')
                    BEGIN
                        ALTER TABLE vi_email_fields
                        ADD module_map_id CHAR(36) NULL
                    END";
    $moduleMapColumnExistResult = $GLOBALS['db']->query($moduleMapColumnExist);

    //Integration module sync fields table
	$allModuleSyncFields = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_email_fields]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_email_fields](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [module_map_id] [CHAR](36) NULL,
                                [email_software] [VARCHAR](255) NULL,
                                [module] [VARCHAR](255) NULL,
                                [fields] [NVARCHAR](MAX) NULL
                             	)
                                END";
    $allModuleSyncFieldsResult = $GLOBALS['db']->query($allModuleSyncFields);

    //Integration module contacts Field Mappings for target list module only
	$contactsFieldMappings = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_integration_contacts_field_mapping]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_integration_contacts_field_mapping](
                                [contacts_field_mapping_id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [module_mapping_id] [VARCHAR](255) NULL,
                                [suitecrm_contacts_module_fields] [VARCHAR](255) NULL,
                                [sendgrid_contacts_module_fields] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$contactsFieldMappingsResult = $GLOBALS['db']->query($contactsFieldMappings);

	//Integration module Field Mappings
	$fieldMappings = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_integration_field_mapping]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_integration_field_mapping](
                                [field_mapping_id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [module_mapping_id] [VARCHAR](255) NULL,
                                [suitecrm_module_fields] [VARCHAR](255) NULL,
                                [es_module_fields] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$fieldMappingsResult = $GLOBALS['db']->query($fieldMappings);

	//Integration Module Mappings
	$moduleMappings = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_module_mapping]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_module_mapping](
                                [module_mapping_id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [title] [VARCHAR](255) NULL,
                                [suitecrm_module] [VARCHAR](255) NULL,
                                [es_module] [VARCHAR](255) NULL,
                                [email_software] [VARCHAR](255) NULL,
                                [status] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$moduleMappingsResult = $GLOBALS['db']->query($moduleMappings);

	//Integration step=3 synchronize
	$synchronize = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_synchronize]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_synchronize](
                                [sync_id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [sync_software] [VARCHAR](255) NULL,
                                [sel_mapping_module_list] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$synchronizeResult = $GLOBALS['db']->query($synchronize);

	//For Accounts Module
	$esAccount = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_accounts_es]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_accounts_es](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [vi_suitecrm_account_id] [VARCHAR](255) NULL,
                                [vi_es_account_id] [VARCHAR](255) NULL,
                                [vi_es_name] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$esAccountResult = $GLOBALS['db']->query($esAccount);

	$deletedColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_accounts_es'
                    AND COLUMN_NAME = 'deleted')
                    BEGIN
                        ALTER TABLE vi_accounts_es
                        ADD deleted SMALLINT(1) NOT NULL DEFAULT 0
                    END";
    $deletedColumnExistResult = $GLOBALS['db']->query($deletedColumnExist);

	//For Asset Module
	$esAsset = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_assets_es]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_assets_es](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [vi_suitecrm_assets_id] [VARCHAR](255) NULL,
                                [vi_es_assets_id] [VARCHAR](255) NULL,
                                [vi_es_name] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$esAssetResult = $GLOBALS['db']->query($esAsset);

	$deletedColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_assets_es'
                    AND COLUMN_NAME = 'deleted')
                    BEGIN
                        ALTER TABLE vi_assets_es
                        ADD deleted SMALLINT(1) NOT NULL DEFAULT 0
                    END";
    $deletedColumnExistResult = $GLOBALS['db']->query($deletedColumnExist);

	//For Campaigns Module
	$esCampaigns = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_campaigns_es]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_campaigns_es](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [vi_suitecrm_campaigns_id] [VARCHAR](255) NULL,
                                [vi_es_campaign_id] [VARCHAR](255) NULL,
                                [vi_es_name] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$esCampaignsResult = $GLOBALS['db']->query($esCampaigns);

	$deletedColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_campaigns_es'
                    AND COLUMN_NAME = 'deleted')
                    BEGIN
                        ALTER TABLE vi_campaigns_es
                        ADD deleted SMALLINT(1) NOT NULL DEFAULT 0
                    END";
    $deletedColumnExistResult = $GLOBALS['db']->query($deletedColumnExist);

	//For Contacts Module
	$esContacts = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_contacts_es]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_contacts_es](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [vi_suitecrm_contact_id] [VARCHAR](255) NULL,
                                [vi_es_contact_id] [VARCHAR](255) NULL,
                                [vi_suitecrm_lead_id] [VARCHAR](255) NULL,
                                [vi_es_name] [VARCHAR](255) NULL,
                                [vi_es_list_id] [VARCHAR](255) NULL,
                                [vi_suitecrm_module] [VARCHAR](255) NULL,
                                [vi_es_lead_id] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$esContactsResult = $GLOBALS['db']->query($esContacts);

	$deletedColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_contacts_es'
                    AND COLUMN_NAME = 'deleted')
                    BEGIN
                        ALTER TABLE vi_contacts_es
                        ADD deleted SMALLINT(1) NOT NULL DEFAULT 0
                    END";
    $deletedColumnExistResult = $GLOBALS['db']->query($deletedColumnExist);

	//For Segments Module
	$esSegments = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_segments_es]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_segments_es](
                                [id] [CHAR](36) NOT NULL PRIMARY KEY,
                                [vi_suitecrm_segments_id] [VARCHAR](255) NULL,
                                [vi_es_segments_id] [VARCHAR](255) NULL,
                                [vi_es_name] [VARCHAR](255) NULL,
                                [deleted] [SMALLINT] NOT NULL DEFAULT 0
                             	)
                                END";
	$esSegmentsResult = $GLOBALS['db']->query($esSegments);

	$deletedColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_segments_es'
                    AND COLUMN_NAME = 'deleted')
                    BEGIN
                        ALTER TABLE vi_segments_es
                        ADD deleted SMALLINT(1) NOT NULL DEFAULT 0
                    END";
    $deletedColumnExistResult = $GLOBALS['db']->query($deletedColumnExist);

	$moduleMapColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_module_mapping'
                    AND COLUMN_NAME = 'batch_record')
                    BEGIN
                        ALTER TABLE vi_module_mapping
                        ADD batch_record INT(11) NOT NULL
                    END";
    $moduleMapColumnExistResult = $GLOBALS['db']->query($moduleMapColumnExist);

    $moduleMapColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_module_mapping'
                    AND COLUMN_NAME = 'batch_management_status')
                    BEGIN
                        ALTER TABLE vi_module_mapping
                        ADD batch_management_status SMALLINT NOT NULL
                    END";
    $moduleMapColumnExistResult = $GLOBALS['db']->query($moduleMapColumnExist);

    $targetListSubpanelModuleColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_module_mapping'
                    AND COLUMN_NAME = 'target_list_subpanel_module')
                    BEGIN
                        ALTER TABLE vi_module_mapping
                        ADD target_list_subpanel_module VARCHAR(255) NULL
                    END";
    $targetListSubpanelModuleColumnExistResult = $GLOBALS['db']->query($targetListSubpanelModuleColumnExist);

    $conditionalOperatorColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_module_mapping'
                    AND COLUMN_NAME = 'conditional_operator')
                    BEGIN
                        ALTER TABLE vi_module_mapping
                        ADD conditional_operator VARCHAR(5) NULL
                    END";
    $conditionalOperatorColumnExistResult = $GLOBALS['db']->query($conditionalOperatorColumnExist);

    $autoSyncColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_automatic_sync'
                    AND COLUMN_NAME = 'auto_sync_ems')
                    BEGIN
                        ALTER TABLE vi_automatic_sync
                        ADD auto_sync_ems SMALLINT NOT NULL
                    END";
    $autoSyncColumnExistResult = $GLOBALS['db']->query($autoSyncColumnExist);

    $autoSyncEMSToSuiteColumnExist ="IF NOT EXISTS(SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                    TABLE_NAME = 'vi_automatic_sync'
                    AND COLUMN_NAME = 'sync_ems_to_suite')
                    BEGIN
                        ALTER TABLE vi_automatic_sync
                        ADD sync_ems_to_suite SMALLINT NOT NULL DEFAULT 0
                    END";
    $autoSyncEMSToSuiteColumnExistResult = $GLOBALS['db']->query($autoSyncEMSToSuiteColumnExist);


    //ems schedule sync
	$emsScheduleSync =  "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_ems_schedule_sync]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                                BEGIN
                                CREATE TABLE [dbo].[vi_ems_schedule_sync](
                                [id] [INT](11) NOT NULL IDENTITY PRIMARY KEY,
                                [ems_software] [VARCHAR](255) NULL,
                                [mapping_id] [CHAR](36) NULL,
                                [batch_record] [INT](11) NULL,
                                [stauts] [SMALLINT] NOT NULL DEFAULT 0,
                                [start_date_time] [DATETIME] NOT NULL,
                                [end_date_time] [DATETIME] NOT NULL
                             	)
                                END";
	$emsScheduleSyncResult = $GLOBALS['db']->query($emsScheduleSync);

	//EMS Condition Table
    $emailSoftwareIntegrationCondition = "IF NOT EXISTS (SELECT * FROM dbo.sysobjects where id = object_id(N'dbo.[vi_ems_conditions]') and OBJECTPROPERTY(id, N'IsTable') = 1)
                    BEGIN

                    CREATE TABLE [dbo].[vi_ems_conditions](
                        [id] [CHAR](36) NOT NULL PRIMARY KEY,
                        [module_path] [VARCHAR](255) NULL,
                        [field] [VARCHAR](255) NULL,
                        [operator] [VARCHAR](255) NULL,
                        [value_type] [VARCHAR](255) NULL,
                        [value] [VARCHAR](255) NULL,
                        [module_mapping_id] [CHAR](36) NULL,
                        [condition_type] [VARCHAR](255) NULL,
                        [date_entered] [DATETIME] NOT NULL,
                        [deleted] [SMALLINT] NOT NULL DEFAULT 0
                    )
                    END";
    $emailSoftwareIntegrationConditionResult = $GLOBALS['db']->query($emailSoftwareIntegrationCondition);
}//end of else

//add data in Schedulers
global $timedate;
$CurrenrDateTime = $timedate->getInstance()->nowDb();
$sel = "SELECT * FROM schedulers WHERE name = 'Run Auto Manual Synchronize for EMS' AND deleted = 0";
$selRow = $GLOBALS['db']->fetchOne($sel);
$flag = 0;
if(!empty($selRow)){
    $id = $selRow['id'];
    $del = "DELETE * FROM schedulers WHERE id = '$id'";
    $delResult = $GLOBALS['db']->query($del);
    if($delResult){
    	$flag = 1;
   	}//end of if
}else{
	$flag = 1;
}//end of else

if($flag == 1){
    //schedular job
    $sched1 = new Scheduler();
    $sched1->name               = 'Run Auto Manual Synchronize for EMS';
    $sched1->job                = 'function::ems';
    $sched1->date_time_start    =  $CurrenrDateTime;
    $sched1->date_time_end      =  null;
    $sched1->job_interval       = '*::*::*::*::*';
    $sched1->status             = 'Active';
    $sched1->created_by         = '1';
    $sched1->modified_user_id   = '1';
    $sched1->catch_up           = '1';
    $sched1->save();
}//end of if

$sel = "SELECT * FROM schedulers WHERE name = 'Run Auto Synchronize From EMS to SuiteCRM' AND deleted = 0";
$selRow = $GLOBALS['db']->fetchOne($sel);
$count = 0;
if(!empty($selRow)){
    $id = $selRow['id'];
    $del = "DELETE * FROM schedulers WHERE id = '$id'";
    $delResult = $GLOBALS['db']->query($del);
    if($delResult){
    	$count = 1;
   	}//end of if
}else{
	$count = 1;
}//end of else

if($count == 1){
    //schedular job
    $schedulerObject = new Scheduler();
    $schedulerObject->name               = 'Run Auto Synchronize From EMS to SuiteCRM';
    $schedulerObject->job                = 'function::emsToSuiteCRM';
    $schedulerObject->date_time_start    =  $CurrenrDateTime;
    $schedulerObject->date_time_end      =  null;
    $schedulerObject->job_interval       = '*::*::*::*::*';
    $schedulerObject->status             = 'Active';
    $schedulerObject->created_by         = '1';
    $schedulerObject->modified_user_id   = '1';
    $schedulerObject->catch_up           = '1';
    $schedulerObject->save();
}//end of if

?>