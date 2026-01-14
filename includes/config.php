<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../globals.php';
?>
<!DOCTYPE html>
<html>

<head>
	<title>Web EA - Configuration</title>
	<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link type="text/css" rel="stylesheet" href="../styles/webea.css" />
	<script src="../js/jquery.min.js"></script>
	<script src="../js/webea.js"></script>
	<script>
		window.onpageshow = function(event) {
			action = $("#config-action").val();
			successMessage = $("#config-successmessage").val();
			if (successMessage !== '') {
				WebeaSuccessMessage($("#config-successmessage").val());
			}
			if (action === 'backup') {
				$("#main-page-overlay").show();
				$("#webea-config-backup-dialog").show();
			}
			disableFields();
		};

		function disableFields() {
			disableFieldsById($("#config-field-protocol"), 'http', 'config-field-sscs_use_ssl,config-field-sscs_enforce_certs')
			disableFieldsById($("#config-field-login_prompt"), 'true', 'config-field-modeluser,config-field-modelpwd');
			disableFieldsById($("#config-field-show_discuss"), 'false', 'config-field-add_discuss,config-field-participate_in_reviews,config-field-use_avatars');
			disableFieldsByAttrib($("#config-field-add_objects"), '[fieldgroup=updatefield]');
			disableFieldsByAttrib($("#config-field-add_object_features"), '[fieldgroup=updatefeatfield]');
			disableFieldsByAttrib($("#config-field-prop_sec_location_visible"), '[id=config-field-prop_sec_location_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_instances_visible"), '[id=config-field-prop_sec_instances_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_relationships_visible"), '[id=config-field-prop_sec_relationships_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_taggedvalues_visible"), '[id=config-field-prop_sec_taggedvalues_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_testing_visible"), '[id=config-field-prop_sec_testing_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_resourcealloc_visible"), '[id=config-field-prop_sec_resourcealloc_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_attributes_visible"), '[id=config-field-prop_sec_attributes_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_operations_visible"), '[id=config-field-prop_sec_operations_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_runstates_visible"), '[id=config-field-prop_sec_runstates_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_changes_visible"), '[id=config-field-prop_sec_changes_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_defects_visible"), '[id=config-field-prop_sec_defects_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_issues_visible"), '[id=config-field-prop_sec_issues_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_tasks_visible"), '[id=config-field-prop_sec_tasks_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_events_visible"), '[id=config-field-prop_sec_events_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_decisions_visible"), '[id=config-field-prop_sec_decisions_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_efforts_visible"), '[id=config-field-prop_sec_efforts_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_risks_visible"), '[id=config-field-prop_sec_risks_expanded]');
			disableFieldsByAttrib($("#config-field-prop_sec_metrics_visible"), '[id=config-field-prop_sec_metrics_expanded]');
		}

		function disableFieldsByAttrib(element, sAttrib) {
			var sValue = $(element).val();
			if (sValue !== '') {
				if (sValue === 'true' || sValue === 'unset') {
					$(sAttrib).removeAttr('disabled');
				} else {
					$(sAttrib).attr('disabled', '');
				}
			}
		}

		function disableFieldsById(field, valueCheck, idsCSV) {
			var aIds = idsCSV.split(',');
			fieldValue = $(field).val();
			if (fieldValue === valueCheck) {
				for (var i = 0; i < aIds.length; i++) {
					fieldToOverride = $('#' + aIds[i]);
					$(fieldToOverride).attr('disabled', '');
				}
			} else {
				for (var i = 0; i < aIds.length; i++) {
					fieldToOverride = $('#' + aIds[i]);
					$(fieldToOverride).removeAttr('disabled');
				}
			}
		}

		function onClickAddModel(element) {
			$("#main-page-overlay").show();
			$("#webea-config-new-dialog").show();
			$("#webea-config-new-textarea").focus();
		}

		function addModel() {
			var friendlyName = $('#webea-config-new-textarea').val();
			if (friendlyName !== '' && friendlyName) {
				$("#config-friendlyname").val(friendlyName);
				$("#config-action").val('add_new');
				document.forms["config-form"].submit();
			}
		}

		function configureModel(selectedModel, friendlyName) {
			if (selectedModel) {
				$("#config-model-name").val(selectedModel);
				$("#config-action").val('configure');
				$("#config-friendlyname").val(friendlyName);
				document.forms["config-form"].submit();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function closeConfig() {
			$("#config-action").val('close_config');
			document.forms["config-form"].submit();
		}

		function cancelConfig() {
			$("#config-action").val('cancel_config');
			document.forms["config-form"].submit();
		}

		function onClickBackup() {
			$("#config-action").val('backup');
			document.forms["config-form"].submit();
		}

		function onClickDeleteModel(modelName, friendlyName) {
			$("#main-page-overlay").show();
			$("#webea-config-delete-dialog").show();
			$("#webea-config-delete-modelname").html(modelName);
			$("#webea-config-delete-modelfriendlyname").html(friendlyName);
		}

		function deleteModel() {
			selectedModel = $("#webea-config-delete-modelname").html();
			friendlyName = $("#webea-config-delete-modelfriendlyname").html();
			if (selectedModel) {
				$("#main-page-overlay").show();
				$("#config-model-name").val(selectedModel);
				$("#config-action").val('delete');
				$("#config-friendlyname").val(friendlyName);
				document.forms["config-form"].submit();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function moveUp(selectedModel, friendlyName, iModelCount, pos) {
			if (selectedModel) {
				var newPos = pos - 1;
				if (newPos < 1)
					newPos = 1;
				$("#config-newpos").val(newPos);
				$("#config-model-name").val(selectedModel);
				$("#config-action").val('move_to');
				$("#config-friendlyname").val(friendlyName);
				document.forms["config-form"].submit();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function moveDown(selectedModel, friendlyName, iModelCount, pos) {
			if (selectedModel) {
				var newPos = parseFloat(pos) + 1;
				$("#config-newpos").val(newPos);
				$("#config-model-name").val(selectedModel);
				$("#config-action").val('move_to');
				$("#config-friendlyname").val(friendlyName);
				document.forms["config-form"].submit();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function onClickMoveTo(selectedModel, friendlyName, iModelCount) {
			$("#config-model-name").val(selectedModel);
			$("#config-move-ok").attr('onclick', 'moveTo(' + iModelCount + ')');
			$("#webea-config-move-body-text").html("Enter a new position number for '" + friendlyName + "' (1 - " + iModelCount + ")");
			$("#main-page-overlay").show();
			$("#webea-config-move-dialog").show();
			$("#webea-config-move-textarea").focus();
		}

		function moveTo(iModelCount) {
			newPos = $('#webea-config-move-textarea').val();
			$("#config-newpos").val(newPos);
			$("#config-action").val('move_to');
			if ($.isNumeric(newPos) &&
				(newPos <= iModelCount) &&
				(newPos >= 0) &&
				(newPos !== '')) {
				document.forms["config-form"].submit();
			} else {
				WebeaErrorMessage('Invalid Position Number');
			}
		}

		function duplicate(selectedModel, friendlyName) {
			if (selectedModel) {
				$("#config-model-name").val(selectedModel);
				$("#config-action").val('duplicate');
				$("#config-friendlyname").val(friendlyName);
				document.forms["config-form"].submit();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function onClickRename(modelName, friendlyName) {
			if (modelName) {
				$("#main-page-overlay").show();
				$("#webea-config-rename-dialog").show();
				$("#webea-config-rename-modelname").html(modelName);
				$("#webea-config-rename-textarea").val(friendlyName);
				$("#webea-config-rename-textarea").focus();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function renameModel() {
			selectedModel = $("#webea-config-rename-modelname").html();
			friendlyName = $('#webea-config-rename-textarea').val();
			if (friendlyName) {
				$("#config-action").val('rename');
				$("#config-model-name").val(selectedModel);
				$("#config-friendlyname").val(friendlyName);
				document.forms["config-form"].submit();
			}
		}

		function setFields(fieldsCSV, value) {
			var aFields = fieldsCSV.split(',');
			for (var i = 0; i < aFields.length; i++) {
				fieldToSet = $(aFields[i]);
				$(fieldToSet).val(value);
			}
			disableFields();
		}

		function onClickClosePopupDialog(sDialogName) {
			$("#main-page-overlay").hide();
			$(sDialogName).hide();
		}

		function onClickSave() {
			$("#config-action").val('save');
			document.forms["config-form"].submit();
		}

		function onRenameKeypressEnter(event) {
			if (event) {
				if (event.keyCode === 13) {
					renameModel();
					return false;
				}
			}
			return true;
		}

		function onMoveKeypressEnter(event) {
			if (event) {
				if (event.keyCode === 13) {
					$("#config-move-ok").click();
					return false;
				}
			}
			return true;
		}

		function onChangeNumberKeypressEnter(event) {
			if (event) {
				if (event.keyCode === 13) {
					$("#config-change-number-ok").click();
					return false;
				}
			}
			return true;
		}

		function onNewModelKeypressEnter(event) {
			if (event) {
				if (event.keyCode === 13) {
					addModel();
					return false;
				}
			}
			return true;
		}

		function onClickChangeModelNumber(modelName, friendlyName) {
			var currentModelNumber;
			if (modelName.substr(0, 5) === 'model') {
				currentModelNumber = modelName.substr(5);
			}
			if (modelName) {
				$("#main-page-overlay").show();
				$("#webea-config-change-number-dialog").show();
				$("#config-model-name").val(modelName);
				$("#webea-config-change-number-textarea").val(currentModelNumber);
				$("#webea-config-change-number-textarea").focus();
				$("#webea-config-change-number-textarea").select();
			} else {
				WebeaAlert('Please select a Model');
			}
		}

		function changeModelNumber() {
			var bAlreadyExists = false;
			newNumber = $('#webea-config-change-number-textarea').val();
			$("#config-newnumber").val(newNumber);
			$("#config-action").val('change_number');
			var newNumberString = 'model' + newNumber;
			$(".model-number").each(function(index) {
				if (newNumberString === $(this).text()) {
					bAlreadyExists = true;
				}
			});
			if ($.isNumeric(newNumber) &&
				(newNumber >= 0)) {
				if (bAlreadyExists) {
					WebeaErrorMessage('"' + newNumberString + '" already exists. Please enter a unique model number.');
				} else {
					document.forms["config-form"].submit();
				}
			} else {
				WebeaErrorMessage('Invalid Value: Please enter a number.');
			}
		}
	</script>
</head>

<body>
	<?php
	error_reporting(E_ERROR | E_WARNING | E_NOTICE);
	echo '<header>';
	echo '	<img src="../images/logo-full.png" alt="EA Logo" align="left"/>';
	echo '</header>';
	include '../checkbrowser.php';
	echo '<div id="main-page-overlay">&nbsp;</div>';
	echo '<div id="webea-messagebox-dialog">';
	echo '<div class="webea-messagebox-title"><div id="webea-messagebox-title-text">' . _h(_glt('WebEA error')) . '</div><input class="webea-dialog-close-button" type="button" onclick="onClickClosePopupDialog(\'#webea-messagebox-dialog\')"/></div>';
	echo '<div class="webea-messagebox-body">';
	echo '<div class="webea-messagebox-line"><div class="webea-messagebox-image"><img src="images/spriteplaceholder.png" class="mainsprite-warning" alt=""></div></div>';
	echo '<div id="webea-messagebox-line-message"><div class="webea-messagebox-line-value" id="webea-messagebox-line-value-message"></div> </div>';
	echo '<div class="webea-about-line-cancel"><input class="webea-main-styled-button webea-about-cancel" type="button" value="' . _h(_glt('OK')) . '" onclick="onClickClosePopupDialog(\'#webea-messagebox-dialog\')"/> </div>';
	echo '</div>';
	echo '</div>';
	$id = 'config-new';
	$title = 'New Model Connection';
	$text = 'Enter a WebEA display name for this model';
	$button = '<input class="webea-main-styled-button webea-dialog-button" value="OK" onclick="addModel()" type="button">';
	$sOnPress = 'onNewModelKeypressEnter(event)';
	WriteTextAreaDialog($id, $title, $text, $button, $sOnPress);
	$id = 'config-rename';
	$title = 'Rename Model Connection';
	$text = 'Enter a new display name for this model<span id="webea-config-rename-modelname">Model</span>';
	$button = '<input class="webea-main-styled-button webea-dialog-button config-button-cancel" type="button" value="' . _h(_glt('Cancel')) . '" onclick="onClickClosePopupDialog(\'#webea-config-rename-dialog\')"/>';
	$button .= '<input class="webea-main-styled-button webea-dialog-button" value="OK" onclick="renameModel()" type="button">';
	$sOnPress = 'onRenameKeypressEnter(event)';
	WriteTextAreaDialog($id, $title, $text, $button, $sOnPress);
	$id = 'config-change-number';
	$title = 'Change Model Number';
	$text = 'Enter a new number for this model.<br><br>Note: Hyperlinks to this model in WebEA include the model number. Changing the model number will cause existing hyperlinks to stop working.<br>';
	$sInput = '<input class="webea-dialog-textarea" id="webea-config-change-number-textarea" type="number" required onkeypress="onChangeNumberKeypressEnter(event)">';;
	$button = '<input class="webea-main-styled-button webea-dialog-button config-button-cancel" type="button" value="' . _h(_glt('Cancel')) . '" onclick="onClickClosePopupDialog(\'#webea-config-change-number-dialog\')"/>';
	$button .= '<input id="config-change-number-ok" class="webea-main-styled-button webea-dialog-button" value="OK" onclick="changeModelNumber()" type="button">';
	$sOnPress = 'onChangeNumberKeypressEnter(event)';
	WriteTextAreaDialog($id, $title, $text, $button, $sOnPress, $sInput);
	$id = 'config-move';
	$title = 'Move Model Configuration';
	$text = 'Enter a new position number for this Model.';
	$sInput = '<input class="webea-dialog-textarea" id="webea-config-move-textarea" type="number" max-length="40" onkeypress="onMoveKeypressEnter(event)">';
	$button = '<input id="config-move-ok"  autofocus="autofocus" class="webea-main-styled-button webea-dialog-button" value="OK" onclick="moveTo()" type="button">';
	$sOnPress = 'onMoveKeypressEnter(event)';
	WriteTextAreaDialog($id, $title, $text, $button, $sOnPress, $sInput);
	$id = 'config-delete';
	$title = 'Delete Model Connection';
	$text = 'Are you sure you want to delete the WebEA Model Connection - <span id="webea-config-delete-modelname">Model</span><span id="webea-config-delete-modelfriendlyname">Model</span>';
	$button = '&nbsp;<input class="webea-main-styled-button webea-dialog-button" value="No" onclick="onClickClosePopupDialog(\'#webea-' . _j($id) . '-dialog\')" style="margin-left: 16px;" type="button">';
	$button .= '&nbsp;<input class="webea-main-styled-button webea-dialog-button" value="Yes" onclick="deleteModel()" type="button">';
	WriteYesNoDialog($id, $title, $text, $button);
	echo '<div class="webea-page-contents">';
	echo '<div class="webea-page-config">';
	echo '<form class="config-form" name="config-form" role="form" action="' . _h($_SERVER['PHP_SELF']) . '" method = "post">';
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		$bIsSaved = 'true';
		$aSettings = ParseINIFile2Array('webea_config.ini', false);
	}
	if (empty($aSettings)) {
		$aSettings['model_list'] = array();
	}
	$sModelName	= '';
	$sModelFriendlyName	= '';
	$sAction	= 'select';
	$sSuccessMessage 	= '';
	$iniIsValid 	= IniIsValid($aSettings);
	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		$sModelName	= SafeGetInternalArrayParameter($_POST, 'modelname', '');
		$sModelFriendlyName	= SafeGetInternalArrayParameter($_POST, 'modelfriendlyname', '');
		$sAction	= SafeGetInternalArrayParameter($_POST, 'action', '');
		$sSettingsJSON	= SafeGetInternalArrayParameter($_POST, 'settingsjson', '');
		$iNewPos	= SafeGetInternalArrayParameter($_POST, 'newpos', '');
		$iNewNumber	= SafeGetInternalArrayParameter($_POST, 'newnumber', '');
		$aSettings	= json_decode($sSettingsJSON, true);
		if ($sAction === 'add_new') {
			$sModelName = GetNewModelNumber($aSettings);
			$aSettings['model_list'][$sModelName] = $sModelFriendlyName;
			$aSettings[$sModelName] = array();
		}
		if ($sAction === 'backup') {
			if (file_exists('webea_config.ini')) {
				$sDateTime = date("Ymd_hms");
				$sBackupSuccess = copy('webea_config.ini', 'webea_config_bak_' . $sDateTime . '.ini');
				if ($sBackupSuccess) {
					$sBackupResults = 'webea_config_bak_' . $sDateTime . '.ini';
				} else {
					$sBackupResults = 'backup_failed';
				}
			} else {
				$sBackupResults = 'file_not_found';
			}
		}
		if ($sAction === 'save') {
			write_to_config($aSettings);
			$sSuccessMessage = 'Saved Model Configurations';
		}
		if (!strIsEmpty($sModelName)) {
			if (isset($aSettings['model_list'])) {
				if ($sAction === 'rename') {
					$aSettings['model_list'][$sModelName] = $sModelFriendlyName;
				} elseif ($sAction === 'duplicate') {
					$sModelToCopy = $sModelName;
					$iModelCount = count($aSettings['model_list']) + 1;
					$sModelName = GetNewModelNumber($aSettings);
					$sCopyName = $sModelFriendlyName . ' - Copy';
					$aSettings['model_list'][$sModelName] = $sCopyName;
					$aSettings[$sModelName] = $aSettings[$sModelToCopy];
					$sSuccessMessage = "Created copy: " . htmlspecialchars($sCopyName);
				} elseif ($sAction === 'delete') {
					unset($aSettings['model_list'][$sModelName]);
					unset($aSettings[$sModelName]);
				} elseif ($sAction === 'move_to') {
					$aModelNameToMove[$sModelName] = $aSettings['model_list'][$sModelName];
					unset($aSettings['model_list'][$sModelName]);
					array_splice_assoc($aSettings['model_list'], $iNewPos - 1, 0, $aModelNameToMove);
					$aModelToMove[$sModelName] = $aSettings[$sModelName];
					unset($aSettings[$sModelName]);
					array_splice_assoc($aSettings, $iNewPos, 0, $aModelToMove);
				} elseif ($sAction === 'change_number') {
					$sOldModelNumber = $sModelName;
					$sNewModelNumber = 'model' . $iNewNumber;
					$aSettings['model_list'] = ChangeArrayKey($aSettings['model_list'], $sOldModelNumber, $sNewModelNumber);
					$aSettings = ChangeArrayKey($aSettings, $sOldModelNumber, $sNewModelNumber);
				} elseif ($sAction === 'close_config') {
					$aNewSettings = array();
					foreach ($_POST as $key => $value) {
						if ($key === 'default_diagram') {
							if (preg_match('/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}$/', $value)) {
								$value = '{' . $value . '}';
							}
						}
						if (($key !== 'action') &&
							($key !== 'modelfriendlyname') &&
							($key !== 'modelname') &&
							($key !== 'settingsjson') &&
							($key !== 'issaved') &&
							($key !== 'newpos') &&
							($key !== 'successmessage') &&
							($key !== 'newnumber')
						) {
							if (
								$value !== 'unset' &&
								!($key === 'cookie_retention' && $value === '365') &&
								!($key === 'recent_search_days' && $value === '3') &&
								!($key === 'mail_days' && $value === '90') &&
								!($key === 'chat_notify_sec' && $value === '30') &&
								!($key === 'wl_period' && $value === '0') &&
								!($key === 'default_main_layout' && $value === 'icon') &&
								!($key === 'sscs_model_user' && $value === '') &&
								!($key === 'sscs_model_pwd' && $value === '') &&
								!($key === 'default_diagram' && $value === '')
							) {
								$aNewSettings[$key] = $value;
							}
						}
					}
					$aSettings[$sModelName] = $aNewSettings;
				}
			}
		}
	}
	echo '<input type="hidden" name="action" id="config-action" value="' . _h($sAction) . '">';
	echo '<input type="hidden" name="modelname" id="config-model-name" value="' . _h($sModelName) . '">';
	echo '<input type="hidden" name="modelfriendlyname" id="config-friendlyname" value="' . _h($sModelFriendlyName) . '">';
	echo '<input type="hidden" name="settingsjson" id="config-settingsjson" value="' . _h(json_encode($aSettings)) . '">';
	echo '<input type="hidden" name="successmessage" id="config-successmessage" value="' . _h($sSuccessMessage) . '">';
	echo '<input type="hidden" name="newpos" id="config-newpos" value="">';
	echo '<input type="hidden" name="newnumber" id="config-newnumber" value="">';
	echo '<div class="webea-config-page-header">WebEA Configuration';
	if ($sAction === 'configure' || $sAction === 'add_new') {
		echo ': ' . $sModelFriendlyName;
	}
	echo '</div>';
	echo '<div id="config-form">';
	if ($iniIsValid) {
		if ($sAction === 'configure' || $sAction === 'cancel_config') {
			$bIsSaved	= SafeGetInternalArrayParameter($_POST, 'issaved', '');
		} else {
			if (($sAction === 'save') ||
				($sAction === 'select') ||
				($sAction === 'backup')
			) {
				$bIsSaved = 'true';
			} else {
				$bIsSaved = 'false';
			}
		}
		$path = './webea_config.ini';
		$ipath = './';
		if (is_writable($path) && is_writable($ipath)) {
			if ($sAction === 'select' || $sAction === 'close_config' || $sAction === 'cancel_config' || $sAction === 'save' || $sAction === 'delete' || $sAction === 'rename' || $sAction === 'move_to' || $sAction === 'duplicate' || $sAction === 'backup' || $sAction === 'change_number') {
				RenderModelList($aSettings, $sModelName, $sAction, $bIsSaved);
			}
			if ($sAction === 'configure' || $sAction === 'add_new') {
				RenderAllSettingsForModel($aSettings, $sModelName, $sModelFriendlyName, $bIsSaved);
			}
		} else {
			echo '<div style="padding: 16px;">';
			echo '<br>';
			if (!is_writable($path)) {
				echo '\'includes/webea_config.ini\' file is not writeable.';
				echo '<br>';
				echo 'Please check that the \'includes/webea_config.ini\' file permissions allow read/write access.';
				echo '<br>';
				echo '<br>';
			}
			if (!is_writable($ipath)) {
				echo '\'includes\' directory is not writeable';
				echo '<br>';
				echo 'Please check that the \'includes\' directory permissions allow read/write (create) access.';
			}
			echo '</div>';
		}
		if ($sAction === 'backup') {
			$id = 'config-backup';
			if ($sBackupResults === 'backup_failed') {
				$title = 'Backup Failed';
				$text = 'Failed to create Backup of webea_config.ini file.';
			} else if ($sBackupResults === 'file_not_found') {
				$title = 'Backup Failed';
				$text = 'webea_config.ini file not found.';
			} else {
				$title = 'Created Backup';
				$text = 'Created Backup of the webea_config.ini file.<br><br>Filename: ' . $sBackupResults;
			}
			$button = '&nbsp<input class="webea-main-styled-button webea-about-cancel" value="OK" onclick="onClickClosePopupDialog(\'#webea-' . _j($id) . '-dialog\')" style="margin-left: 16px;" type="button">';
			WriteYesNoDialog($id, $title, $text, $button);
		}
		echo '<input type="hidden" name="issaved" id="config-issaved" value="' . _h($bIsSaved) . '">';
	} else {
		echo '<br>Invalid webea_config.ini file<br><br>';
		echo 'Please update the ini file so that the \'model_list\' and model \'settings\' entries match.<br>';
		echo 'Or, create a new webea_config.ini file in the webea \'includes\' directory.<br><br>';
		echo 'Current [model_list] entries:<br>';
		echo '';
		foreach ($aSettings['model_list'] as $key => $value) {
			echo _h($key);
			echo '<br>';
		}
		echo '<br>';
		echo 'Current model [settings] entries:';
		echo '<br>';
		$i = 0;
		foreach ($aSettings as $key => $value) {
			if ($i > 0) {
				echo _h($key);
				echo '<br>';
			}
			$i++;
		}
	}
	echo '</div>';
	echo '</form>';
	echo '	</div>';
	echo '</div>';
	echo '<footer>';
	echo '	<div id="main-footer-config">';
	echo '	<div class="main-footer-copyright"> Copyright &copy; ' . _h(g_csCopyRightYears) . ' Sparx Systems Pty Ltd.  All rights reserved.</div> ';
	echo ' 	<div class="main-footer-version"> WebEA v' . _h(g_csWebEAVersion) . '</div>';
	echo '	</div>';
	echo '</footer>';
	echo '<div id="webea-success-message">';
	echo '<div class="webea-success-message-text" id="webea-success-message-text"></div>';
	echo '</div>';
	echo '<div id="webea-error-message">';
	echo '<div class="webea-error-message-text" id="webea-error-message-text"></div>';
	echo '</div>';
	function IniIsValid($aSettings)
	{
		return true;
	}
	function write_to_config($aSettings)
	{
		$path = './webea_config.ini';
		$fp = fopen($path, 'w');
		$ini = '; -------------------------------------------------------------------------------------------------' . PHP_EOL;
		$ini .= ';   Configuration file for Web EA' . PHP_EOL;
		$ini .= ';' . PHP_EOL;
		$ini .= ';     Notes: ' . PHP_EOL;
		$ini .= ';	- Comments start with \';\'' . PHP_EOL;
		$ini .= ';	- values that contains non-alphanumeric characters, need to be enclosed in double-quotes (")' . PHP_EOL;
		$ini .= ';	- at a minimum each model should define:  sscs_protocol, sscs_server, sscs_port & sscs_db_alias' . PHP_EOL;
		$ini .= ';' . PHP_EOL;
		$ini .= ';	  IMPORTANT always use IP numbers for server for best performance.  ' . PHP_EOL;
		$ini .= ';	  	    Using \'localhost\' can introduce long delays for each OSLC call.' . PHP_EOL;
		$ini .= ';' . PHP_EOL;
		$ini .= '; -------------------------------------------------------------------------------------------------' . PHP_EOL;
		fwrite($fp, $ini);
		fwrite($fp, arr2ini($aSettings));
		fclose($fp);
	}
	function arr2ini(array $a, array $parent = array())
	{
		$out = '';
		foreach ($a as $k => $v) {
			if (is_array($v)) {
				$sec = array_merge((array) $parent, (array) $k);
				$out .= PHP_EOL;
				if (join('.', $sec) === 'model_list') {
					$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
				} else {
					$out .= '[' . join('.', $sec) . ' : settings]' . PHP_EOL;
				}
				$out .= arr2ini($v, $sec);
			} else {
				$out .= "$k = \"$v\"" . PHP_EOL;
			}
		}
		return $out;
	}
	function RenderModelList($aSettings, $sSelectedModel, $sAction, $bIsSaved)
	{
		if (isset($aSettings['model_list'])) {
			$iCnt = count($aSettings['model_list']);
			echo '<div id="config-model-list" class="webea-page-pane2">';
			echo '<table id="config-table">';
			echo '<tr><th title="Position Number">Pos</th><th title="Model Number (a unique identifier which is used in WebEA hyperlinks)">Model #</th><th>Name</th><th>Actions</th><th>Move</th></tr>';
			$isChecked = '';
			$i = 1;
			foreach ($aSettings['model_list'] as $sKey => $model) {
				if ($sKey === $sSelectedModel) {
					$isChecked = 'checked';
				} else {
					$isChecked = '';
				}
				echo '<tr class="config-model-row">';
				echo '<td title="Position Number">';
				echo $i;
				echo '</td>';
				echo '<td>';
				echo '<span class="model-number" title="Model Number (a unique identifier which is used in WebEA hyperlinks)">' . $sKey . '</span>';
				echo '<div class="config-icon" onclick="onClickChangeModelNumber(\'' . _j($sKey) . '\',\'' . _j($model) . '\')" title="Change Model Number"><img alt="" class="config-sprite config-cog-icon" src="../images/spriteplaceholder.png"></div>';
				echo '</td>';
				echo '<td>' . htmlspecialchars($model) . '</td>';
				$model = ConvertStringToParameter($model);
				echo '<td>';
				echo '<div class="config-icon" onclick="configureModel(\'' . _j($sKey) . '\',\'' . _j($model) . '\')" title="Edit"><img alt="" class="config-sprite config-edit-icon" src="../images/spriteplaceholder.png"></div>';
				echo '<div class="config-icon" onclick="onClickRename(\'' . _j($sKey) . '\',\'' . _j($model) . '\')" title="Rename"><img alt="" class="config-sprite config-rename-icon"  src="../images/spriteplaceholder.png"></div>';
				echo '<div class="config-icon" onclick="duplicate(\'' . _j($sKey) . '\',\'' . _j($model) . '\')" title="Copy"><img alt="" class="config-sprite config-copy-icon"  src="../images/spriteplaceholder.png"></div>';
				echo '<div class="config-icon" onclick="onClickDeleteModel(\'' . _j($sKey) . '\',\'' . _j($model) . '\')" title="Delete"><img alt="" class="config-sprite config-delete-icon" src="../images/spriteplaceholder.png"></div>';
				echo '</td>';
				echo '<td>';
				echo '<div class="config-icon" onclick="moveUp(\'' . _j($sKey) . '\',\'' . _j($model) . '\',\'' . _j($iCnt) . '\',\'' . _j($i) . '\')" title="Move Up"><img alt="" class="config-sprite config-up-icon"  src="../images/spriteplaceholder.png"></div>';
				echo '<div class="config-icon" onclick="moveDown(\'' . _j($sKey) . '\',\'' . _j($model) . '\',\'' . _j($iCnt) . '\',\'' . _j($i) . '\')" title="Move Down"><img alt="" class="config-sprite config-down-icon"  src="../images/spriteplaceholder.png"></div>';
				echo '<div class="config-icon" onclick="onClickMoveTo(\'' . _j($sKey) . '\',\'' . _j($model) . '\',\'' . _j($iCnt) . '\')" title="Move to..."><img alt="" class="config-sprite config-moveto-icon"  src="../images/spriteplaceholder.png"></div>';
				echo '</td>';
				echo '</tr>';
				$i++;
			}
			echo '<tr>';
			echo '<td>';
			echo '<img alt="" class="config-sprite config-add-icon" src="../images/spriteplaceholder.png">';
			echo '</td>';
			echo '<td><a class="w3-link" onclick="javascript:onClickAddModel(this)">' . _h('<Add a Model>') . '</a></td>';
			echo '<td></td><td></td><td></td><td></td><td></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			if ($bIsSaved === 'true') {
				$sDisableSave = 'disabled="true"';
				$sDisableBackup = '';
			} else {
				$sDisableSave = '';
				$sDisableBackup = 'disabled="true"';
			}
			echo '<div class="webea-page-pane3">';
			echo '	<input class="webea-main-styled-button webea-config-save" ' . _h($sDisableSave) . ' type="button" value="' . _h(_glt('Save')) . '" onclick="onClickSave()"/>';
			echo '<input class="webea-main-styled-button webea-config-ok" ' . _h($sDisableBackup) . ' value="Create Backup" onclick="onClickBackup()" type="button">';
			echo '<a style="float:right;padding: 8px;" href="../login.php">Go to WebEA login</a>';
			echo '</div>';
		}
	}
	function RenderAllSettingsForModel($aSettings, $sModelName, $sModelFriendlyName, $bIsSaved)
	{
		if (!strIsEmpty($sModelName)) {
			echo '<div class="webea-page-pane2">';
			echo '<div id="connection-options-header" class="config-field-header">Connection Options</div>';
			echo '<div class="config-field-line" style="height: 2.6em;"><div class="config-field-label2">Protocol <span class="field-label-required">&nbsp;*</span></div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_protocol', 'http');
			$sTooltip = 'Should match the Protocol which is selected when accessing this model via EA\'s Clould Connection option.';
			echo '<select id="config-field-protocol" name="sscs_protocol" class="webea-main-styled-combo config-field-value config-field-combo1" title="' . _h($sTooltip) . '" onchange="disableFieldsById(this,\'http\',\'config-field-sscs_use_ssl,config-field-sscs_enforce_certs\')">';
			if ($sValue === 'https') {
				echo '<option value="http">HTTP</option>';
				echo '<option value="https" selected="">HTTPS</option>';
			} else {
				echo '<option value="http" selected="">HTTP</option>';
				echo '<option value="https">HTTPS</option>';
			}
			echo '</select></div>';
			echo '<div class="config-field-line"><div class="config-field-label">Server <span class="field-label-required">&nbsp;*</span></div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_server', 'localhost');
			$sTooltip = 'Should match the Server name or IP address which is entered when accessing this model via EA\'s Clould Connection option.';
			echo '<input id="config-field-server" class="config-field-value webea-main-styled-textbox" name="sscs_server" title="' . _h($sTooltip) . '" maxlength="100" style="width: 300px" value="' . _h($sValue) . '">';
			echo '</div>';
			echo '<div class="config-field-line"><div class="config-field-label">Port <span class="field-label-required">&nbsp;*</span></div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_port', '804');
			$sTooltip = 'Should match the Port which is entered when accessing this model via EA\'s Clould Connection option.';
			echo '<input id="config-field-port" class="config-field-value webea-main-styled-textbox" name="sscs_port" title="' . _h($sTooltip) . '" type="number" maxlength="6" value="' . _h($sValue) . '" style="width:90px;">';
			echo '</div>';
			echo '<div class="config-field-line"><div class="config-field-label">Model Name / Alias<span class="field-label-required">&nbsp;*</span></div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_db_alias', '');
			$sTooltip = 'Should match the Model Name which is entered when accessing this model via EA\'s Clould Connection option.';
			echo '<input id="config-field-dbalias" class="webea-main-styled-textbox config-field-value" name="sscs_db_alias" title="' . _h($sTooltip) . '" maxlength="100" style="width: 300px" value="' . _h($sValue) . '">';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_enforce_certs', 'unset');
			$sTooltip = '"If you are using HTTPS and a self signed certificate then this should be set to No."';
			WriteYesNoCombo('sscs_enforce_certs', 'Validate SSL Certificates', $sValue, 'true', 'title=' . $sTooltip);
			echo '<div class="config-field-line"><div class="config-field-label">OSLC Access Code</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_access_code', '');
			$sTooltip = 'When configuring a Pro Cloud Server model an optional \'OSLC Access Code\' can be defined. If the model has an \'OSLC Access Code\' assigned then it will need to be entered in this field also.';
			echo '<input id="config-field-oslcaccesscode" class="webea-main-styled-textbox config-field-value" name="sscs_access_code" title="' . _h($sTooltip) . '" maxlength="100" style="width: 300px" value="' . _h($sValue) . '">';
			echo '</div>';
			echo '<div class="config-required-field-message">* Required for all model connections</div>';
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header">Authentication Options</div>';
			echo '<div class="config-field-line"><div class="config-field-label">Authentication Code</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'auth_code', '');
			$sTooltip = 'Optionally, enter an Authentication code which will need to be entered when accessing this model via WebEA. When left blank, no Authenticaiton code will be required.';
			echo '<input id="config-field-authcode" class="webea-main-styled-textbox config-field-value" type="password" name="auth_code" title="' . _h($sTooltip) . '" maxlength="30" style="width: 200px" value="' . _h($sValue) . '">';
			echo '</div>';
			echo '<div class="config-field-line"><div class="config-field-label">Model User</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_model_user', '');
			$sTooltip = 'Optionally, if user security is enabled enter a User Name/Login to automatically login with.';
			echo '<input id="config-field-modeluser" class="webea-main-styled-textbox config-field-value" name="sscs_model_user" title="' . _h($sTooltip) . '" maxlength="100" style="width: 200px" value="' . _h($sValue) . '">';
			echo '</div>';
			echo '<div class="config-field-line"><div class="config-field-label">User Password</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'sscs_model_pwd', '');
			$sTooltip = 'Enter the password for the Model User specified above.';
			echo '<input id="config-field-modelpwd" class="webea-main-styled-textbox config-field-value" type="password" name="sscs_model_pwd" title="' . _h($sTooltip) . '" maxlength="100" style="width: 200px" value="' . _h($sValue) . '">';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'login_allow_blank_pwd', 'unset');
			$sTooltip = '"Select whether blank passwords can be used."';
			WriteYesNoCombo('login_allow_blank_pwd', 'Allow blank passwords', $sValue, 'false', 'title=' . $sTooltip);
			echo '</div>';
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header">General Options</div>';
			$sSelectAttributes = 'id="config-mainlayout-section" name="default_main_layout" class="webea-main-styled-combo config-field-value config-field-combo1" ';
			$sSelectAttributes .= 'title = "Select the default layout style which will be used for containers such as packages."';
			$aOptions = [
				['icon', 'Icon'],
				['list', 'List'],
				['notes', 'Notes'],
				['unset', 'List (default)']
			];
			$sSelected	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'default_main_layout', 'unset');
			echo '<div class="config-field-line" style="height: 2.6em;"><div class="config-field-label2">Default Package Layout </div>';
			echo WriteDropDown($sSelectAttributes, $aOptions, $sSelected);
			echo ' </div>';
			$sSelectAttributes = 'id="config-field-object-order" name="object_order" class="webea-main-styled-combo config-field-value config-field-combo1" ';
			$sSelectAttributes .= 'title = "Select the order for elements in the Browser and Object List views. Select either Alphpabetical, Free Sort (apply custom order from EA), or Object Type (sort by type, then apply the custom order from EA)"';
			$aOptions = [
				['1', 'Alphabetical'],
				['2', 'Free Sort'],
				['3', 'Object Type'],
				['unset', 'Object Type (default)']
			];
			$sSelected	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'object_order', 'unset');
			echo '<div class="config-field-line" style="height: 2.6em;"><div class="config-field-label2">Object Order </div>';
			echo WriteDropDown($sSelectAttributes, $aOptions, $sSelected);
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'favorites_as_home', 'unset');
			$sTooltip = '"When set to Yes, the WebEA home button will display the User\'s \'Favorites\' Packages in the Browser/Package View. The Browser\'s Up button is also hidden, helping to keep navigation within the Favorites package/s. Note, when set to Yes, this overrides the Default Diagram setting."';
			WriteYesNoCombo('favorites_as_home', 'Favorites as Home', $sValue, 'false', 'title=' . $sTooltip);
			echo '<div class="config-field-line"><div class="config-field-label">Default Diagram</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'default_diagram', '');
			$sTooltip = 'Enter the GUID (including curly braces and hyphens) of a daigram to be used as the default/home diagram.';
			echo '<input id="config-field-defaultdiagram" class="webea-main-styled-textbox config-field-value" name="default_diagram" title="' . _h($sTooltip) . '" maxlength="45" style="width: 300px" value="' . _h($sValue) . '" style="width:300px;">';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'miniprops_navigates', 'unset');
			$sTooltip = '"When set to Yes, if the Properties View is displayed then Hyperlinks and Navigation Cells will navigate to the appropriate target instead of displaying properties of the element."';
			WriteYesNoCombo('miniprops_navigates', 'Mini Properties Navigates', $sValue, 'true', 'title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'navigate_to_diagram', 'unset');
			$sTooltip = '"When set to Yes, clicking a Composite element on a diagram will navigate to the target diagram instead of showing the element\'s properties"';
			WriteYesNoCombo('navigate_to_diagram', 'Diagram Elements Navigate', $sValue, 'true', 'title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_search', 'unset');
			$sTooltip = '"Select Yes to show the Search dropdown menu."';
			WriteYesNoCombo('show_search', 'Show Search', $sValue, 'true', 'title=' . $sTooltip);
			echo '<div class="config-field-line"><div class="config-field-label">Recent Search Days </div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'recent_search_days', '3');
			$sTooltip = '"Defines the number of days the predefined \'Recent ...\' searches should consider."';
			echo '<input id="config-field-recentdays" class="webea-main-styled-textbox config-field-value" name="recent_search_days" title=' . _h($sTooltip) . ' type="number" maxlength="3" style="width: 60px" value="' . _h($sValue) . '" style="width:90px;">';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_watchlist', 'unset');
			$sTooltip = '"Select Yes to show the menu option to view the Watchlist."';
			WriteYesNoCombo('show_watchlist', 'Show Watchlist', $sValue, 'true', 'title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_browser', 'unset');
			$sTooltip = '"Select Yes to show the Browser by default. Note, this may be overriden based on the web browser resolution. E.g. The WebEA Browser is automatically hidden when using a low resolution device (such as a mobile phone)"';
			WriteYesNoCombo('show_browser', 'Show Browser', $sValue, 'true', 'title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_propertiesview', 'unset');
			$sTooltip = '"Select Yes to show the Properties View by default. Note, this may be overriden based on the web browser resolution. E.g. The Properties View is automatically hidden when using a low resolution device (such as a mobile phone)"';
			WriteYesNoCombo('show_propertiesview', 'Show Properties View', $sValue, 'true', 'title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_path_button', 'unset');
			$sTooltip = '"Select Yes to show the Path button in the Navbar. The Path button is used to display a list of Packages/Objects from the current Object up to the Model Root."';
			WriteYesNoCombo('show_path_button', 'Show Path Button', $sValue, 'true', 'title=' . $sTooltip);
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header">Collaboration Options</div>';
			echo '<div class="config-field-setmultiple">';
			echo '<a href="javascript:setFields(\'[fieldgroup = collabfield]\',\'true\')">All Yes</a> | <a href="javascript:setFields(\'[fieldgroup = collabfield]\',\'unset\')">All No</a>';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_comments', 'unset');
			$sTooltip = '"Should Comments be accessible in WebEA"';
			WriteYesNoCombo('show_comments', 'Show Comments', $sValue, 'false', 'fieldgroup="collabfield" title=' . $sTooltip);
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_chat', 'unset');
			$sTooltip = '"Should Model Chat be accessible in WebEA"';
			WriteYesNoCombo('show_chat', 'Show Model Chat', $sValue, 'false', 'fieldgroup="collabfield" title=' . $sTooltip);
			echo '<div class="config-field-line"><div class="config-field-label">Chat Notification Frequency</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'chat_notify_sec', '30');
			$sTooltip = '"Defines how frequently (in seconds) WebEA will check for new Chat messages and display/update the Chat Notification. Default Value: 30"';
			echo '<input id="config-field-recentdays" class="webea-main-styled-textbox config-field-value" name="chat_notify_sec" title=' . $sTooltip . ' type="number" maxlength="3" style="width: 60px" value="' . _h($sValue) . '">';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_mail', 'unset');
			$sTooltip = '"Should Model Mail be accessible in WebEA"';
			WriteYesNoCombo('show_mail', 'Show Model Mail', $sValue, 'false', 'fieldgroup="collabfield" title=' . $sTooltip);
			echo '<div class="config-field-line"><div class="config-field-label">Mail Time Period (days)</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'mail_days', '90');
			$sTooltip = '"Defines how many days worth of messages are displayed in the Model Mail Inbox and Sent Mail lists"';
			echo '<input id="config-field-recentdays" class="webea-main-styled-textbox config-field-value" name="mail_days" title=' . $sTooltip . ' type="number" maxlength="3" style="width: 60px" value="' . _h($sValue) . '">';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'show_discuss', 'unset');
			$sTooltip = '"Should discussions be displayed in WebEA"';
			WriteYesNoCombo('show_discuss', 'Show Discussions', $sValue, 'false', 'fieldgroup="collabfield" onchange="disableFieldsById(this, \'false\', \'config-field-add_discuss,config-field-participate_in_reviews,config-field-use_avatars\')" title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_discuss', 'unset');
			$sTooltip = '"Can discussions and replies be created in WebEA (only applies if \'Show Discussions\' is set to Yes)"';
			WriteYesNoCombo('add_discuss', 'Add Discussions', $sValue, 'false', 'fieldgroup="collabfield" title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'participate_in_reviews', 'unset');
			$sTooltip = '"Can users Join Reviews in WebEA (only applies if \'Show Discussions\' is set to Yes)"';
			WriteYesNoCombo('participate_in_reviews', 'Participate in Reviews', $sValue, 'false', 'fieldgroup="collabfield" title=' . $sTooltip);
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'use_avatars', 'unset');
			$sTooltip = '"Should discussions use custom Avatar icons in WebEA (only applies if \'Show Discussions\' is set to Yes)"';
			WriteYesNoCombo('use_avatars', 'Use Avatars', $sValue, 'true', 'fieldgroup="collabfield" title=' . $sTooltip);
			echo '</div>';
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header collapsible-plusminussection-closed config-field-header-plusminus" onclick="OnTogglePlusMinusState(this, \'config-updates-section\')">Object Creation and Update Options</div>';
			echo '<div id="config-updates-section" style="display: none;">';
			echo '<div class="config-field-setmultiple">';
			echo '<a href="javascript:setFields(\'[fieldgroup = updatefield]\,[id = config-field-add_objects]\',\'true\')">All Yes</a> | <a href="javascript:setFields(\'[fieldgroup = updatefield]\,[id = config-field-add_objects]\',\'unset\')">All No</a>';
			echo '</div>';
			$sUpdateObjects	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objects', 'unset');
			WriteYesNoCombo('add_objects', 'Can add Objects', $sUpdateObjects, 'false', 'onchange="disableFieldsByAttrib(this, \'[fieldgroup=updatefield]\')"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'edit_object_notes', 'unset') : 'unset';
			WriteYesNoCombo('edit_object_notes', 'Edit Object Notes', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_package', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_package', 'Can add Packages', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_diagrams', 'unset') : 'unset';
			WriteYesNoCombo('add_diagrams', 'Can add Diagrams', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_review', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_review', 'Can add Reviews', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_actor', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_actor', 'Can add Actors', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_change', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_change', 'Can add Changes', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_component', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_component', 'Can add Components', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_feature', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_feature', 'Can add Features', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_issue', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_issue', 'Can add Issues', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_node', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_node', 'Can add Nodes', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_requirement', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_requirement', 'Can add Requirements', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_task', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_task', 'Can add Tasks', $sValue, 'false', 'fieldgroup="updatefield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objecttype_usecase', 'unset') : 'unset';
			WriteYesNoCombo('add_objecttype_usecase', 'Can add Use Cases', $sValue, 'false', 'fieldgroup="updatefield"');
			echo '</div>';
			echo '</div>';
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header collapsible-plusminussection-closed config-field-header-plusminus" onclick="OnTogglePlusMinusState(this, \'config-updatefeatures-section\')">Object Feature Update Options</div>';
			echo '<div id="config-updatefeatures-section" style="display: none;">';
			echo '<div class="config-field-setmultiple">';
			echo '<a href="javascript:setFields(\'[fieldgroup = updatefeatfield]\,[id = config-field-add_object_features]\',\'true\')">All Yes</a> | <a href="javascript:setFields(\'[fieldgroup = updatefeatfield]\,[id = config-field-add_object_features]\',\'unset\')">All No</a>';
			echo '</div>';
			$sValue = SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_object_features', 'unset');
			$sTooltip = '"Can users add features such as Tests, Changes, etc"';
			WriteYesNoCombo('add_object_features', 'Can add Object Features', $sValue, 'false', 'title=' . $sTooltip . ' onchange="disableFieldsByAttrib(this, \'[fieldgroup=updatefeatfield]\')"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'edit_objectfeature_tests', 'unset') : 'unset';
			WriteYesNoCombo('edit_objectfeature_tests', 'Can edit Tests', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'edit_objectfeature_resources', 'unset') : 'unset';
			WriteYesNoCombo('edit_objectfeature_resources', 'Can edit Resources', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'edit_objectfeature_tests', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_tests', 'Can add Tests', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'edit_objectfeature_resources', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_resources', 'Can add Resources', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_features', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_features', 'Can add Features', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_changes', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_changes', 'Can add Changes', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_documents', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_documents', 'Can add Documents', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_defects', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_defects', 'Can add Defects', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_issues', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_issues', 'Can add Issues', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_tasks', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_tasks', 'Can add Tasks', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			$sValue	= strIsTrue($sUpdateObjects) ? SafeGetArrayItem2DimByName($aSettings, $sModelName, 'add_objectfeature_risks', 'unset') : 'unset';
			WriteYesNoCombo('add_objectfeature_risks', 'Can add Risks', $sValue, 'false', 'fieldgroup="updatefeatfield"');
			echo '</div>';
			echo '</div>';
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header collapsible-plusminussection-closed config-field-header-plusminus" onclick="OnTogglePlusMinusState(this, \'config-visprops-section\')">Property Section Options</div>';
			echo '<div id="config-visprops-section" style="display: none;">';
			echo '<table id="prop-section-table"><tbody>';
			echo '<tr><th>Property Section</th><th>Visible</th></tr>';
			echo '<tr>';
			echo '<td></td>';
			echo '<td><a href="javascript:setFields(\'[fieldgroup=propertyvisible]\',\'unset\')">All Yes</a> | <a href="javascript:setFields(\'[fieldgroup=propertyvisible]\',\'false\')">All No</a></td>';
			echo '</tr>';
			WritePropertySectionCombos($aSettings, $sModelName, 'Location', 'location');
			WritePropertySectionCombos($aSettings, $sModelName, 'Instances', 'instances');
			WritePropertySectionCombos($aSettings, $sModelName, 'Relationships', 'relationships');
			WritePropertySectionCombos($aSettings, $sModelName, 'Tagged Values', 'taggedvalues');
			WritePropertySectionCombos($aSettings, $sModelName, 'Testing', 'testing');
			WritePropertySectionCombos($aSettings, $sModelName, 'Resource Allocation', 'resourcealloc');
			WritePropertySectionCombos($aSettings, $sModelName, 'Attributes', 'attributes');
			WritePropertySectionCombos($aSettings, $sModelName, 'Operations', 'operations');
			WritePropertySectionCombos($aSettings, $sModelName, 'Files', 'files');
			WritePropertySectionCombos($aSettings, $sModelName, 'Run States', 'runstates');
			WritePropertySectionCombos($aSettings, $sModelName, 'Features', 'features');
			WritePropertySectionCombos($aSettings, $sModelName, 'Changes', 'changes');
			WritePropertySectionCombos($aSettings, $sModelName, 'Documents', 'documents');
			WritePropertySectionCombos($aSettings, $sModelName, 'Defects', 'defects');
			WritePropertySectionCombos($aSettings, $sModelName, 'Issues', 'issues');
			WritePropertySectionCombos($aSettings, $sModelName, 'Tasks', 'tasks');
			WritePropertySectionCombos($aSettings, $sModelName, 'Events', 'events');
			WritePropertySectionCombos($aSettings, $sModelName, 'Decisions', 'decisions');
			WritePropertySectionCombos($aSettings, $sModelName, 'Efforts', 'efforts');
			WritePropertySectionCombos($aSettings, $sModelName, 'Risks', 'risks');
			WritePropertySectionCombos($aSettings, $sModelName, 'Metrics', 'metrics');
			echo '</tbody></table>';
			echo '</div>';
			echo '</div>';
			echo '<div class="config-field-headerandsection">';
			echo '<div class="config-field-header collapsible-plusminussection-closed config-field-header-plusminus" onclick="OnTogglePlusMinusState(this, \'config-watchlist-section\')">Default Watchlist Options</div>';
			echo '<div id="config-watchlist-section" style="display: none;">';
			echo '<div class="config-field-line"><div class="config-field-label">Days to Watch </div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_period', '0');
			echo '<input id="config-field-wlperiod" class="webea-main-styled-textbox config-field-value" name="wl_period" type="number" maxlength="3" value="' . _h($sValue) . '" style="width:90px;">';
			echo '</div>';
			echo '<div class="config-field-line"><div class="config-field-label">Cookie Retention </div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'cookie_retention', '365');
			echo '<input id="config-field-cookieretention" class="webea-main-styled-textbox config-field-value" name="cookie_retention" type="number" maxlength="3" value="' . _h($sValue) . '" style="width:90px;">';
			echo '</div>';
			echo '<br>';
			echo '<div class="config-field-setmultiple">';
			echo '<a href="javascript:setFields(\'[fieldgroup = watchlistfield]\',\'true\')">All Yes</a> | <a href="javascript:setFields(\'[fieldgroup = watchlistfield]\',\'unset\')">All No</a>';
			echo '</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_recent_discuss', 'unset');
			WriteYesNoCombo('wl_recent_discuss', 'Recent Discussions', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_recent_reviews', 'unset');
			WriteYesNoCombo('wl_recent_reviews', 'Recent Reviews', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_recent_diagram', 'unset');
			WriteYesNoCombo('wl_recent_diagram', 'Recent Diagrams', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_recent_element', 'unset');
			WriteYesNoCombo('wl_recent_element', 'Recent Elements', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_resalloc_active', 'unset');
			WriteYesNoCombo('wl_resalloc_active', 'Active resource tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_resalloc_today', 'unset');
			WriteYesNoCombo('wl_resalloc_today', 'Ending resource tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_resalloc_overdue', 'unset');
			WriteYesNoCombo('wl_resalloc_overdue', 'Overdue resource tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_test_recentpass', 'unset');
			WriteYesNoCombo('wl_test_recentpass', 'Recently passed tests', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_test_recentfail', 'unset');
			WriteYesNoCombo('wl_test_recentfail', 'Recently failed tests', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_test_recentdefer', 'unset');
			WriteYesNoCombo('wl_test_recentdefer', 'Recently deferred tests', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_test_recentnotchk', 'unset');
			WriteYesNoCombo('wl_test_recentnotchk', 'Recent tests not checked', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_test_notrun', 'unset');
			WriteYesNoCombo('wl_test_notrun', 'Tests not run', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_feature_verified', 'unset');
			WriteYesNoCombo('wl_feature_verified', 'Verified features', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_feature_requested', 'unset');
			WriteYesNoCombo('wl_feature_requested', 'Recently requested features', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_feature_completed', 'unset');
			WriteYesNoCombo('wl_feature_completed', 'Recently completed features', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_feature_new', 'unset');
			WriteYesNoCombo('wl_feature_new', 'New features', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_feature_incomplete', 'unset');
			WriteYesNoCombo('wl_feature_incomplete', 'Incomplete features', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_change_verified', 'unset');
			WriteYesNoCombo('wl_change_verified', 'Verified changes', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_change_requested', 'unset');
			WriteYesNoCombo('wl_change_requested', 'Recently requested changes', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_change_completed', 'unset');
			WriteYesNoCombo('wl_change_completed', 'Recently completed changes', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_change_new', 'unset');
			WriteYesNoCombo('wl_change_new', 'New changes', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_change_incomplete', 'unset');
			WriteYesNoCombo('wl_change_incomplete', 'Incomplete changes', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_document_verified', 'unset');
			WriteYesNoCombo('wl_document_verified', 'Verified documents', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_document_requested', 'unset');
			WriteYesNoCombo('wl_document_requested', 'Recently requested documents', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_document_completed', 'unset');
			WriteYesNoCombo('wl_document_completed', 'Recently completed documents', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_document_new', 'unset');
			WriteYesNoCombo('wl_document_new', 'New documents', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_document_incomplete', 'unset');
			WriteYesNoCombo('wl_document_incomplete', 'Incomplete documents', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_defect_verified', 'unset');
			WriteYesNoCombo('wl_defect_verified', 'Verified defects', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_defect_requested', 'unset');
			WriteYesNoCombo('wl_defect_requested', 'Recently reported defects', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_defect_completed', 'unset');
			WriteYesNoCombo('wl_defect_completed', 'Recently resolved defects', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_defect_new', 'unset');
			WriteYesNoCombo('wl_defect_new', 'New defects', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_defect_incomplete', 'unset');
			WriteYesNoCombo('wl_defect_incomplete', 'Incomplete defects', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_issue_verified', 'unset');
			WriteYesNoCombo('wl_issue_verified', 'Verified issues', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_issue_requested', 'unset');
			WriteYesNoCombo('wl_issue_requested', 'Recently reported issues', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_issue_completed', 'unset');
			WriteYesNoCombo('wl_issue_completed', 'Recently resolved issues', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_issue_new', 'unset');
			WriteYesNoCombo('wl_issue_new', 'New issues', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_issue_incomplete', 'unset');
			WriteYesNoCombo('wl_issue_incomplete', 'Incomplete issues', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_task_verified', 'unset');
			WriteYesNoCombo('wl_task_verified', 'Verified tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_task_requested', 'unset');
			WriteYesNoCombo('wl_task_requested', 'Recently reported tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_task_completed', 'unset');
			WriteYesNoCombo('wl_task_completed', 'Recently resolved tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_task_new', 'unset');
			WriteYesNoCombo('wl_task_new', 'New tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_task_incomplete', 'unset');
			WriteYesNoCombo('wl_task_incomplete', 'Incomplete tasks', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_event_requested', 'unset');
			WriteYesNoCombo('wl_event_requested', 'Recently reported events', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_event_high', 'unset');
			WriteYesNoCombo('wl_event_high', 'High Priority events', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_event_incomplete', 'unset');
			WriteYesNoCombo('wl_event_incomplete', 'Incomplete events', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '<div class="config-field-line-spacer">&nbsp;</div>';
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_decision_verified', 'unset');
			WriteYesNoCombo('wl_decision_verified', 'Verified decisions', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_decision_requested', 'unset');
			WriteYesNoCombo('wl_decision_requested', 'Recently reported decisions', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_decision_completed', 'unset');
			WriteYesNoCombo('wl_decision_completed', 'Recently resolved decisions', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_decision_new', 'unset');
			WriteYesNoCombo('wl_decision_new', 'New decisions', $sValue, 'false', 'fieldgroup="watchlistfield"');
			$sValue	= SafeGetArrayItem2DimByName($aSettings, $sModelName, 'wl_decision_incomplete', 'unset');
			WriteYesNoCombo('wl_decision_incomplete', 'Incomplete decisions', $sValue, 'false', 'fieldgroup="watchlistfield"');
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="webea-page-pane3">';
		echo '<input class="webea-main-styled-button webea-config-ok" value="OK" onclick="closeConfig()" type="button">';
		echo '<input class="webea-main-styled-button webea-config-close" type="button" value="' . _h(_glt('Cancel')) . '" onclick="cancelConfig()"/>';
		echo '</div>';
	}
	function WriteYesNoCombo($sFieldName, $sDisplayName, $sValue, $sDefault = '', $sExtraAttribs = '')
	{
		if ($sDefault === 'true') {
			$sDefault = 'Yes';
		} elseif ($sDefault === 'false') {
			$sDefault = 'No';
		}
		echo '<div class="config-field-line">';
		if ($sDisplayName !== '') {
			echo '<div class="config-field-label2">' . $sDisplayName . ' </div>';
		}
		echo '<select id="config-field-' . _h($sFieldName) . '" name="' . _h($sFieldName) . '" class="webea-main-styled-combo config-field-value config-field-combo1" ' . $sExtraAttribs . '>';
		if (($sValue === 'no') || ($sValue === 'false')) {
			echo '<option value="true">Yes</option>';
			echo '<option value="false" selected="">No</option>';
			if (!strIsEmpty($sDefault)) {
				echo '<option value="unset">' . _h($sDefault) . ' (default)</option>';
			}
		} elseif (($sValue === 'yes') || ($sValue === 'true')) {
			echo '<option value="true" selected="">Yes</option>';
			echo '<option value="false">No</option>';
			if (!strIsEmpty($sDefault)) {
				echo '<option value="unset">' . _h($sDefault) . ' (default)</option>';
			}
		} elseif (($sValue === 'unset')) {
			echo '<option value="true">Yes</option>';
			echo '<option value="false">No</option>';
			if (!strIsEmpty($sDefault)) {
				echo '<option value="unset" selected="">' . _h($sDefault) . ' (default)</option>';
			}
		}
		echo '</select></div>';
	}
	function WritePropertySectionCombos($aSettings, $sModelName, $sLabel, $sSection)
	{
		echo '<tr>';
		echo '<td style="padding-right: 30px;">';
		echo '<div class="config-field-label2">' . _h($sLabel) . ' </div>';
		echo '</td>';
		$sFieldName1 = 'prop_sec_' . $sSection . '_visible';
		$sValue1 = SafeGetArrayItem2DimByName($aSettings, $sModelName, $sFieldName1, 'unset');
		echo '<td>';
		echo '<select id="config-field-' . _h($sFieldName1) . '" name="' . _h($sFieldName1) . '" class="webea-main-styled-combo config-field-inline-combo config-field-combo1" fieldgroup="propertyvisible"' . '>';
		if (($sValue1 === 'no') || ($sValue1 === 'false')) {
			echo '<option value="true">Yes</option>';
			echo '<option value="false" selected="">No</option>';
			echo '<option value="unset">Yes (default)</option>';
		} elseif (($sValue1 === 'yes') || ($sValue1 === 'true')) {
			echo '<option value="true" selected="">Yes</option>';
			echo '<option value="false">No</option>';
			echo '<option value="unset">Yes (default)</option>';
		} elseif (($sValue1 === 'unset')) {
			echo '<option value="true" >Yes</option>';
			echo '<option value="false">No</option>';
			echo '<option value="unset" selected="">Yes (default)</option>';
		}
		echo '</select>';
		echo '</td>';
		echo '</tr>';
	}
	function WriteTextAreaDialog($id, $title, $text, $button, $sOnPress = '', $sInput = '')
	{
		$title = _glt($title);
		$text = _glt($text);
		if ($sOnPress !== '') {
			$sOnPress = 'onkeypress="' . $sOnPress . '"';
		}
		if ($sInput === '') {
			$sInput = '<input class="webea-dialog-textarea" id="webea-' . _h($id) . '-textarea" type="text" max-length="40" ' . $sOnPress . '>';
		}
		echo '<div class="webea-dialog" id="webea-' . _h($id) . '-dialog">';
		echo '<div class="webea-dialog-title"><div class="webea-dialog-title-text" id="webea-' . _h($id) . '-title-text">' . _h($title) . '</div><input class="webea-dialog-close-button" type="button" onclick="onClickClosePopupDialog(\'#webea-' . _j($id) . '-dialog\')"/></div>';
		echo '<div class="webea-dialog-body">';
		echo '<div class="webea-dialog-line" style="padding-top: 20px;" id="webea-' . _h($id) . '-body-text">' . $text . '</div>';
		echo '<div class="webea-dialog-line">' . $sInput . '</div>';
		echo '<div class="webea-dialog-button-line">' . $button . '</div>';
		echo '</div>';
		echo '</div>';
	}
	function WriteDropDown($sSelectAttributes, $aOptions, $sSelected)
	{
		$sHTML = '';
		$sHTML .= '<select ' . $sSelectAttributes . '>';
		foreach ($aOptions as $aOption) {
			$sValue = $aOption[0];
			$sLabel = $aOption[1];
			$sSelectedAtt = '';
			if ($sValue === $sSelected) {
				$sSelectedAtt = ' selected=""';
			}
			$sHTML .= '<option value="' . $sValue . '"' . $sSelectedAtt . '>';
			$sHTML .= $sLabel;
			$sHTML .= '</option>';
		}
		$sHTML .= '</select>';
		return $sHTML;
	}
	function array_splice_assoc(&$input, $offset, $length, $replacement)
	{
		$replacement = (array) $replacement;
		$key_indices = array_flip(array_keys($input));
		if (isset($input[$offset]) && is_string($offset)) {
			$offset = $key_indices[$offset];
		}
		if (isset($input[$length]) && is_string($length)) {
			$length = $key_indices[$length] - $offset;
		}
		$input = array_slice($input, 0, $offset, TRUE)
			+ $replacement
			+ array_slice($input, $offset + $length, NULL, TRUE);
	}
	function GetNewModelNumber($aSettings)
	{
		$iModelCount = count($aSettings['model_list']);
		$iModelCount = $iModelCount + 1;
		$i = 1;
		$bFoundNewName = false;
		while (($i <= $iModelCount) &&
			($bFoundNewName === false)
		) {
			$sPossibleModelNum = 'model' . $i;
			$bFoundNewName = !array_key_exists($sPossibleModelNum, $aSettings['model_list']);
			$i++;
		}
		return $sPossibleModelNum;
	}
	function ChangeArrayKey($array, $old_key, $new_key)
	{
		if (! array_key_exists($old_key, $array))
			return $array;
		$keys = array_keys($array);
		$keys[array_search($old_key, $keys)] = $new_key;
		return array_combine($keys, $array);
	}
	?>
</body>

</html>