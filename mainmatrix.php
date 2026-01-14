<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
if (!isset($webea_page_parent_mainview)) {
	exit();
}
$sRootPath = dirname(__FILE__);
require_once $sRootPath . '/globals.php';
SafeStartSession();
if (isset($_SESSION['authorized']) === false) {
	$sErrorMsg = _glt('Your session appears to have timed out');
	setResponseCode(440, $sErrorMsg);
	exit();
}
include('./data_api/get_matrixprofilesettings.php');
$bOSLCErrorOccurred = false;
if ($aSettings !== null && !empty($aSettings)) {
	if (!strIsEmpty(SafeGetArrayItem1Dim($aSettings, 'lastoslcerror'))) {
		$bOSLCErrorOccurred = true;
	}
}
if (empty($aSettings) || $bOSLCErrorOccurred) {
	echo '<div id="matrix-profile-error">';
	if ($g_sLastOSLCErrorMsg !== null) {
		echo $g_sLastOSLCErrorMsg;
	}
	echo '</div>';
	return;
}
include('./data_api/get_matrixcontents.php');
ksort($aSourceElements);
ksort($aTargetElements);
if ($aSettings !== null && !empty($aSettings)) {
	echo '<div id="matrix-header-top">';
	echo '<table id="matrix-header-table">';
	echo '<tr>';
	echo '<td class="matrix-header-label">' . _glt('Source') . ':</td>';
	echo '<td id="matrix-header-source-field" class="matrix-header-field" title="' . htmlspecialchars($aSettings['sourcepackagename']) . '" onclick="load_object(\'' . $aSettings['sourcepackageguid'] . '\',\'\',\'\',\'\',\'\',\'\') "><img alt="" title="Package" src="images/spriteplaceholder.png" class="element16-package">  ' . htmlspecialchars($aSettings['sourcepackagename']) . '</td>';
	echo '<td class="matrix-header-label">' . _glt('Type') . ':</td>';
	echo '<td class="matrix-header-field">' . $aSettings['sourcetype'] . '</td>';
	echo '<td id="matrix-header-source-type-label" class="matrix-header-label">' . _glt('Link Type') . ':</td>';
	echo '<td id="matrix-header-linktype-field" class="matrix-header-field">' . $aSettings['linktype'] . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td class="matrix-header-label">' . _glt('Target') . ':</td>';
	echo '<td id="matrix-header-target-field" class="matrix-header-field" title="' . htmlspecialchars($aSettings['targetpackagename']) . '" onclick="load_object(\'' . $aSettings['targetpackageguid'] . '\',\'\',\'\',\'\',\'\',\'\') "><img alt="" title="Package" src="images/spriteplaceholder.png" class="element16-package">  ' . htmlspecialchars($aSettings['targetpackagename']) . '</td>';
	echo '<td id="matrix-header-target-type-label" class="matrix-header-label">Type:</td>';
	echo '<td class="matrix-header-field">' . $aSettings['targettype'] . '</td>';
	echo '<td class="matrix-header-label">' . _glt('Direction') . ':</td>';
	echo '<td class="matrix-header-field">' . $aSettings['linkdirection'] . '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	if (
		$aSettings['sourcepackagename'] === '' ||
		$aSettings['targetpackagename'] === '' ||
		$aSettings['sourcetype'] === '' ||
		$aSettings['targettype'] === '' ||
		$aSettings['linktype'] === '' ||
		$aSettings['linkdirection'] === ''
	) {
		echo '<div id="matrix-profile-error">';
		echo _glt('Matrix profile is incomplete');
		echo '</div>';
	} else {
		echo '<div id="matrix-top-left">';
		echo '<table class="matrix-table">';
		echo '    <thead>';
		echo ' <tr class="matrix-tr">';
		echo '       <th id= "matrix-header-top-left" class="matrix-header-x-th" style="min-width: 150px">';
		echo '<div id="source-package" title="' . htmlspecialchars($aSettings['sourcepackagename']) . '" onclick="load_object(\'' . $aSettings['sourcepackageguid'] . '\',\'\',\'\',\'\',\'\',\'\') ">' . htmlspecialchars($aSettings['sourcepackagename']) . '</div>';
		echo '<div id="target-package" title="' . htmlspecialchars($aSettings['targetpackagename']) . '" onclick="load_object(\'' . $aSettings['targetpackageguid'] . '\',\'\',\'\',\'\',\'\',\'\') ">' . htmlspecialchars($aSettings['targetpackagename']) . '</div>';
		echo '	</th>';
		echo ' </tr>';
		echo '</thead>';
		echo '</table>';
		echo '</div>';
	}
}
$sImageURL	= '';
$sHref = '';
if ($aTargetElements !== null && !empty($aTargetElements)) {
	echo '<div id="matrix-header-x-container">';
	echo '<div id="matrix-header-x">';
	echo '<table class="matrix-table">';
	echo '    <thead>';
	echo ' <tr class="matrix-tr">';
	foreach ($aTargetElements as $sElement) {
		$sImageURL	= GetObjectImagePath($sElement['targettype'], 'element', '', '', 16);
		$sHref = 'load_object(\'' . $sElement['targetguid'] . '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($sElement['targetname']) . '\',\'' . $sImageURL . '\')';
		echo '<th class="matrix-header-x-th" title="' . htmlspecialchars($sElement['targetname']) . '" onclick="' . $sHref . '"><div class="rotate"><div class="source-name">' . htmlspecialchars($sElement['targetname']) . '</div></div></th>';
	}
	echo ' </tr>';
	echo '</thead>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
}
$sImageURL	= '';
$sHref = '';
if ($aSourceElements !== null && !empty($aSourceElements)) {
	echo '<div id="matrix-header-y-container">';
	echo '<div id="matrix-header-y">';
	echo '<table class="matrix-table">';
	echo '<tbody>';
	foreach ($aSourceElements as $sElement) {
		$sImageURL	= GetObjectImagePath($sElement['sourcetype'], 'element', '', '', 16);
		$sHref = 'load_object(\'' . $sElement['sourceguid'] . '\',\'\',\'\',\'\',\'' . ConvertStringToParameter($sElement['sourcename']) . '\',\'' . $sImageURL . '\')';
		echo '<tr class="matrix-tr">';
		echo '<th class="matrix-header-y-th" title= "' . htmlspecialchars($sElement['sourcename']) . '" onclick="' . $sHref . '">' . htmlspecialchars($sElement['sourcedisplayname']) . '</th>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
}
echo '<div id="matrix-contents" class="matrix-contents">';
echo '<table class="matrix-table">';
echo '<tbody>';
if ($aSourceElements !== null) {
	foreach ($aSourceElements as $aSourceElement) {
		echo '<tr class="matrix-tr">';
		if ($aTargetElements !== null) {
			foreach ($aTargetElements as $aTargetElement) {
				$bHasRelationship = false;
				$sDirection = '';
				$sRelGUID = '';
				$sRelName = '';
				if ($aRelationships !== null) {
					foreach ($aRelationships as $aRelationship) {
						if ($aSettings['linkdirection'] === 'Source -> Target') {
							if ((($aSourceElement['sourceguid'] === $aRelationship['sourceresourceidentifier']) && ($aTargetElement['targetguid'] === $aRelationship['targetresourceidentifier']))) {
								$sOverlay = '';
								if (isset($aRelationship['overlay'])) {
									$sOverlay = $aRelationship['overlay'];
								}
								$sDirection = $aSettings['linkdirection'];
								$sRelGUID = $aRelationship['identifier'];
								$sRelType = $aRelationship['type'];
								$sRelName = $aRelationship['name'];
								$bHasRelationship = true;
							}
						} else if ($aSettings['linkdirection'] === 'Target -> Source') {
							if (($aSourceElement['sourceguid'] === $aRelationship['targetresourceidentifier']) && ($aTargetElement['targetguid'] === $aRelationship['sourceresourceidentifier'])) {
								$sOverlay = '';
								if (isset($aRelationship['overlay'])) {
									$sOverlay = $aRelationship['overlay'];
								}
								$sDirection = $aSettings['linkdirection'];
								$sRelGUID = $aRelationship['identifier'];
								$sRelType = $aRelationship['type'];
								$sRelName = $aRelationship['name'];
								$bHasRelationship = true;
							}
						} else {
							if ((($aSourceElement['sourceguid'] === $aRelationship['sourceresourceidentifier']) && ($aTargetElement['targetguid'] === $aRelationship['targetresourceidentifier']))
								|| (($aSourceElement['sourceguid'] === $aRelationship['targetresourceidentifier']) && ($aTargetElement['targetguid'] === $aRelationship['sourceresourceidentifier']))
							) {
								$sOverlay = '';
								if (isset($aRelationship['overlay'])) {
									$sOverlay = $aRelationship['overlay'];
								}
								if (($aSourceElement['sourceguid'] === $aRelationship['sourceresourceidentifier']) && ($aTargetElement['targetguid'] === $aRelationship['targetresourceidentifier'])) {
									$sDirection = 'Source -> Target';
									foreach ($aRelationships as $aRel) {
										if (($aSourceElement['sourceguid'] === $aRel['targetresourceidentifier']) && ($aTargetElement['targetguid'] === $aRel['sourceresourceidentifier'])) {
											$sDirection = 'Both';
										}
									}
								}
								if (($aSourceElement['sourceguid'] === $aRelationship['targetresourceidentifier']) && ($aTargetElement['targetguid'] === $aRelationship['sourceresourceidentifier'])) {
									$sDirection = 'Target -> Source';
									foreach ($aRelationships as $aRel) {
										if (($aSourceElement['sourceguid'] === $aRel['sourceresourceidentifier']) && ($aTargetElement['targetguid'] === $aRel['targetresourceidentifier'])) {
											$sDirection = 'Both';
										}
									}
								}
								$sRelGUID = $aRelationship['identifier'];
								$sRelType = $aRelationship['type'];
								$sRelName = $aRelationship['name'];
								$bHasRelationship = true;
							}
						}
					}
				}
				$sImageURL = '';
				$sRelDisplayName = '';
				$sHyperlink = '';
				if ($bHasRelationship === true) {
					$sImageURL = GetObjectImagePath($sRelType, 'Connector', '', '', '16');
					$sRelDisplayName = $sRelType . ' connector ' . $sRelName;
					$sHyperlink = 'onclick="load_object(\'' . $sRelGUID . '\',\'false\',\'\',\'\',\'' . ConvertStringToParameter($sRelDisplayName) . '\',\'' . $sImageURL . '\')"';
					if (($sOverlay !== '')) {
						echo '<td class="matrix-td matrix-overlay-cell" ' . $sHyperlink . ' title="' . $sOverlay . '"><div class="matrix-overlay-cell">' . $sOverlay . '</div></td>';
					} else {
						if ($sDirection === 'Source -> Target') {
							echo '<td class="matrix-td matrix-connector-cell" ' . $sHyperlink . '><img alt="" src="images/spriteplaceholder.png" class="mainsprite-matrixup"></td>';
						} elseif ($sDirection === 'Target -> Source') {
							echo '<td class="matrix-td matrix-connector-cell" ' . $sHyperlink . '><img alt="" src="images/spriteplaceholder.png" class="mainsprite-matrixleft"></td>';
						} elseif ($sDirection === 'Both') {
							echo '<td class="matrix-td matrix-connector-cell" ' . $sHyperlink . '><img alt="" src="images/spriteplaceholder.png" class="mainsprite-matrixboth"></td>';
						} else {
							echo '<td class="matrix-td matrix-both" ' . $sHyperlink . '>' . 'X' . '</td>';
						}
					}
				} else {
					echo '<td class="matrix-td">' . '' . '</td>';
				}
			}
		}
		echo '</tr>';
	}
}
echo '</tbody>';
echo '</table>';
echo '</div>';
?>
<script>
	$(document).ready(function() {
		$("#matrix-contents").scroll(function() {
			$('#matrix-header-y').css({
				'top': (-$(this).scrollTop())
			});
		});
		$("#matrix-contents").scroll(function() {
			$('#matrix-header-x').css({
				'left': -$(this).scrollLeft()
			});
		});
	});
</script>