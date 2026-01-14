/* --------------------------------------------------------
    This is a part of the Sparx Systems Pro Cloud Server.
    Copyright (C) Sparx Systems Pty Ltd
    All rights reserved.
  
    This source code can be used only under terms and 
    conditions of the accompanying license agreement.
   -------------------------------------------------------- */
gc_sHelpLocation =
  "http://sparxsystems.com/enterprise_architect_user_guide/14.0/";
g_bIsLoggingOff = false;
g_bJSDebugging = false;
var g_TimerRef;
var g_sLastGUID;
var g_sLastHasChild;
var g_sLastLinkType;
var g_sLastHyper;
var g_sLastName;
var g_sLastImageURL;
$(document).ajaxComplete(function (event, xhr, options) {
  if (
    options.url.search("get_setting.php") == -1 &&
    options.url.search("select_model.php") == -1 &&
    options.url.search(/login.*\.php/) == -1
  ) {
    TimerCheck();
  }
});
function initialise_parameter_str(sParameter, sValue) {
  if (typeof sParameter === "undefined") {
    sParameter = sValue;
  }
  return sParameter;
}
function isDefined(x) {
  var undefined;
  return x !== undefined;
}
function TimerCheck() {
  $.get("./data_api/get_setting.php?varname=timer_check", function (data) {
    var sData = String(data);
    var a = sData.split("&");
    var sAuthorized = a[0];
    var sSessionModelNo = a[1];
    sAuthorized = str_trim(sAuthorized);
    if (typeof sAuthorized === "undefined") {
      sAuthorized = "";
    }
    if (typeof sSessionModelNo === "undefined") {
      sSessionModelNo = "";
    }
    if (sAuthorized === "" || sAuthorized === "false") {
      show_fatal_timeout(false);
    } else {
      sSessionModelNo = str_trim(sSessionModelNo);
      var sPageModelNo = $("#page-built-with-model-no").text();
      if (sSessionModelNo !== "" && sSessionModelNo !== sPageModelNo) {
        show_fatal_timeout(false);
      }
    }
  });
}
function OnLogoff(event) {
  g_bIsLoggingOff = true;
  clear_last_page();
  document.location.href = "logout.php";
}
function clear_last_page() {
  var sSessionID = get_cookie_value("webea_session");
  var sPageModelNo = $("#page-built-with-model-no").text();
  document.cookie = "webea_lastpage_" + sPageModelNo + "=;path=/;";
}
function load_navbar(sGUID, sHasChild, sLinkType, sHyper, sName, sImageURL) {
  var ele = $("#main-navbar");
  if (ele != null) {
    $.ajax({
      type: "POST",
      cache: false,
      url: "navbar.php",
      data: {
        guid: sGUID,
        haschild: sHasChild,
        linktype: sLinkType,
        hyper: sHyper,
        name: sName,
        imageurl: sImageURL,
      },
      beforeSend: function () {
        $("#navbar-busy-loader").show();
      },
      success: function (sNavBarHTML) {
        document.getElementById("main-navbar").innerHTML = sNavBarHTML;
        load_server_systemoutput();
        if (sGUID === "searchresults") {
          var sSearchCnt = $("#search-results-count").text();
          if (sSearchCnt !== "") {
            var sNavbarName = $("#navbar-current-name-text").text();
            sNavbarName =
              '<img alt="" src="images/spriteplaceholder.png" class="mainsprite-searchresults">&nbsp;' +
              sNavbarName +
              "&nbsp;(" +
              sSearchCnt +
              ")";
            document.getElementById("navbar-current-name-text").innerHTML =
              sNavbarName;
          }
        } else if (sGUID === "watchlistresults") {
          var sSearchCnt = $("#watchlist-results-count").text();
          if (sSearchCnt !== "") {
            var sNavbarName = $("#navbar-current-name-text").text();
            sNavbarName =
              '<img alt="" src="images/spriteplaceholder.png" class="mainsprite-watchlistresults">&nbsp;' +
              sNavbarName +
              "&nbsp;(" +
              sSearchCnt +
              ")";
            document.getElementById("navbar-current-name-text").innerHTML =
              sNavbarName;
          }
        }
      },
      error: function (jqXHR) {
        Write2Console("load_navbar:" + jqXHR.statusText);
      },
      complete: function () {
        $("#navbar-busy-loader").hide();
      },
    });
  }
}
function focus_search_term() {
  if ("ontouchstart" in document.documentElement) {
    return;
  }
  var field = $("input[id='search-criteria-field']", ".search-form");
  if (field.length) {
    field.focus();
  }
}
function onClickSearchFor(element) {
  if (typeof element === "undefined") {
    return;
  }
  refresh_search_form();
}
function refresh_search_form() {
  var frmSearch = $(".search-form");
  if (typeof frmSearch === "undefined") {
    return;
  }
  var bDisable = false;
  var fldCategory = $("#search-searchfor-combo", frmSearch).find(":selected");
  var fldCriteria = $("input[id='search-criteria-field']", ".search-form");
  var fldSearchType = $("#search-searchtype-combo", frmSearch);
  if (
    typeof fldCategory !== "undefined" &&
    fldCategory.val() === "discussion"
  ) {
    bDisable = true;
  }
  if (typeof fldCriteria !== "undefined") {
    fldCriteria.attr("disabled", bDisable);
  }
  if (typeof fldSearchType !== "undefined") {
    fldSearchType.attr("disabled", bDisable);
  }
}
$.fn.serializeAndEncode = function () {
  return $.map(this.serializeArray(), function (val) {
    return [val.name, encodeURIComponent(val.value)].join("=");
  }).join("&");
};
function OnFormRunCustomSearch(event, sInName) {
  event.preventDefault();
  var sPayload = "search=custom&" + $(".search-form").serializeAndEncode();
  sPayload = sPayload.replace("&recent=Any", "");
  if (sPayload !== "") {
    replace_history_state(
      "search",
      "",
      "",
      sPayload,
      "Search",
      "images/element16/search.png",
      "WebEA - Search",
      "index.php"
    );
    load_object(
      "searchresults",
      "",
      "",
      sPayload,
      sInName,
      "images/element16/searchresults.png"
    );
  }
}
function OnRunPredefinedSearch(sParameters, sInName) {
  var sPayload = "search=predefined&" + sParameters;
  sPayload = sPayload.replace("&recent=Any", "");
  if (sPayload !== "") {
    load_object2(
      "searchresults",
      "",
      "",
      sPayload,
      sInName,
      "images/element16/searchresults.png",
      true
    );
  }
}
function OnFormRunAddRootPackage(event) {
  event.preventDefault();
  SaveNicEditorData("add-element-notes-field");
  var sPayload = "action=addrootpackage&" + $(".add-element-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "addrootpackageengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) === "success:") {
          webea_success_message(sResults.substr(9));
          refresh_current();
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunAddElement(event) {
  event.preventDefault();
  SaveNicEditorData("add-element-notes-field");
  var sPayload = "action=addelement&" + $(".add-element-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "addelementengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) === "success:") {
          webea_success_message(sResults.substr(9));
          refresh_current();
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function str_trim(str) {
  str = String(str);
  if (str != "") {
    str = str.replace(/^\s+|\s+$/gm, "");
  }
  return str;
}
function OnFormRunAddElementTest(event) {
  event.preventDefault();
  SaveNicEditorData("add-elementtest-notes-field");
  SaveNicEditorData("add-elementtest-input-field");
  SaveNicEditorData("add-elementtest-acceptance-field");
  SaveNicEditorData("add-elementtest-results-field");
  var sPayload =
    "action=addelementtest&" + $(".add-elementtest-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "addelementtestengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          ClearInputField(
            document.getElementById("add-elementtest-name-field")
          );
          ClearInputField(
            document.getElementById("add-elementtest-notes-field")
          );
          ClearInputField(
            document.getElementById("add-elementtest-input-field")
          );
          ClearInputField(
            document.getElementById("add-elementtest-acceptance-field")
          );
          ClearInputField(
            document.getElementById("add-elementtest-results-field")
          );
          ClearNicEditor("add-elementtest-notes-field");
          ClearNicEditor("add-elementtest-input-field");
          ClearNicEditor("add-elementtest-acceptance-field");
          ClearNicEditor("add-elementtest-results-field");
          webea_success_message(sResults.substr(9));
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunAddElementResAlloc(event) {
  event.preventDefault();
  SaveNicEditorData("add-elementresalloc-notes-field");
  var sPayload =
    "action=addelementresalloc&" + $(".add-elementresalloc-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "addelementresallocengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          webea_success_message(sResults.substr(9));
          ClearInputField(
            document.getElementById("add-elementresalloc-resource-field")
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-startdate-field")
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-enddate-field")
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-percentcomp-field"),
            "0"
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-expectedtime-field"),
            "0"
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-allocatedtime-field"),
            "0"
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-expendedtime-field"),
            "0"
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-notes-field")
          );
          ClearInputField(
            document.getElementById("add-elementresalloc-history-field")
          );
          ClearNicEditor("add-elementresalloc-notes-field");
          ClearNicEditor("add-elementresalloc-history-field");
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunAddElementChgMgmt(event) {
  event.preventDefault();
  SaveNicEditorData("add-elementchgmgmt-notes-field");
  SaveNicEditorData("add-elementchgmgmt-history-field");
  var sPayload =
    "action=addelementchgmgmt&" + $(".add-elementchgmgmt-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "addelementchgmgmtengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          webea_success_message(sResults.substr(9));
          var dtToday = new Date();
          var sYYYY = dtToday.getFullYear().toString();
          var sMM = (dtToday.getMonth() + 1).toString();
          var sDD = dtToday.getDate().toString();
          var sToday =
            sYYYY +
            "-" +
            (sMM[1] ? sMM : "0" + sMM[0]) +
            "-" +
            (sDD[1] ? sDD : "0" + sDD[0]);
          ClearInputField(
            document.getElementById("add-elementchgmgmt-name-field")
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-status-field")
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-priority-field")
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-author1-field")
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-date1-field"),
            sToday
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-author2-field"),
            ""
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-date2-field"),
            ""
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-type-field"),
            ""
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-version-field"),
            "1.0"
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-weight-field"),
            "0"
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-notes-field")
          );
          ClearInputField(
            document.getElementById("add-elementchgmgmt-history-field")
          );
          ClearNicEditor("add-elementchgmgmt-notes-field");
          ClearNicEditor("add-elementchgmgmt-history-field");
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunEditElementNote(event) {
  event.preventDefault();
  SaveNicEditorData("edit-elementnote-notes-field");
  var sPayload =
    "action=editelementnote&" + $(".edit-elementnote-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "editelementnoteengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          webea_success_message(sResults.substr(9));
          MoveToPrevItemInNavigationHistory();
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunEditElementTest(event) {
  event.preventDefault();
  SaveNicEditorData("edit-elementtest-notes-field");
  SaveNicEditorData("edit-elementtest-input-field");
  SaveNicEditorData("edit-elementtest-acceptance-field");
  SaveNicEditorData("edit-elementtest-results-field");
  var sPayload =
    "action=editelementtest&" + $(".edit-elementtest-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "editelementtestengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          webea_success_message(sResults.substr(9));
          MoveToPrevItemInNavigationHistory();
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunEditElementResAlloc(event) {
  event.preventDefault();
  SaveNicEditorData("edit-elementresalloc-notes-field");
  SaveNicEditorData("edit-elementresalloc-history-field");
  var sPayload =
    "action=editelementresalloc&" + $(".edit-elementresalloc-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "editelementresallocengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          webea_success_message(sResults.substr(9));
          MoveToPrevItemInNavigationHistory();
        } else {
          if (check_fatal_error(sResults)) {
            webea_error_message(sResults);
          }
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnFormRunWatchListConfig(event) {
  event.preventDefault();
  var sPayload =
    "action=watchlistconfig&" + $(".watchlist-config-form").serialize();
  var requestSettings = {
    type: "POST",
    url: "savewatchlistengine.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults != "") {
        if (sResults.substr(0, 8) == "success:") {
          webea_success_message(sResults.substr(9));
          MoveToPrevItemInNavigationHistory();
        } else {
          webea_error_message(sResults);
        }
        load_server_systemoutput();
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
    },
    error: function (jqXHR) {
      webea_error_message(jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
function OnIntegerFieldLostFocus(sFieldID) {
  var ele = document.getElementById(sFieldID);
  if (ele != null) {
    var sValue = ele.value;
    sValue = sValue.replace(/[^0-9]/g, "");
    if (sValue != "") {
      if (sValue != ele.value) {
        ele.value = sValue;
      }
    }
  }
}
function OnDateFieldLostFocus(sFieldID) {
  var ele = document.getElementById(sFieldID);
  if (ele != null) {
    var sValue = str_trim(ele.value);
    if (sValue != "") {
      var dtNow = new Date();
      if ((sValue.length = 1 && sValue.length <= 2)) {
        sValue = (dtNow.getMonth() + 1).toString() + "-" + sValue;
      }
      if (sValue.length >= 3 && sValue.length <= 5) {
        sValue = dtNow.getFullYear().toString() + "-" + sValue;
      }
      sValue = sValue.replace(/[\/. ]/g, "-");
      if (
        sValue.length == 8 &&
        sValue.substring(2, 3) == "-" &&
        sValue.slice(5, 6) == "-"
      ) {
        sValue = dtNow.getFullYear().toString().substr(0, 2) + sValue;
      }
      aDateParts = sValue.split("-");
      if (aDateParts.length == 3) {
        if (checkdate(aDateParts[0], aDateParts[1], aDateParts[2])) {
          var dt = new Date(
            aDateParts[0],
            aDateParts[1],
            aDateParts[2],
            0,
            0,
            0,
            0
          );
          sValue =
            dt.getFullYear() +
            "-" +
            ("0" + dt.getMonth()).slice(-2) +
            "-" +
            ("0" + dt.getDate()).slice(-2);
        }
      }
      if (sValue != ele.value) {
        ele.value = sValue;
      }
    }
  }
}
function checkdate(year, month, day) {
  var d = new Date(year, month, day);
  return d.getFullYear() == year && d.getMonth() == month && d.getDate() == day;
}
function ClearInputField(e, sNewValue) {
  if (!e) {
    return;
  }
  sNewValue = initialise_parameter_str(sNewValue, "");
  if (e.tagName) {
    switch (e.tagName.toLowerCase()) {
      case "input":
        switch (e.type) {
          case "radio":
          case "checkbox":
            e.checked = false;
            break;
          case "button":
          case "submit":
          case "image":
            break;
          default:
            e.value = sNewValue;
            break;
        }
        break;
      case "select":
        e.selectedIndex = 0;
        break;
      case "textarea":
        e.innerHTML = sNewValue;
        e.value = sNewValue;
        break;
    }
  }
}
function ClearNicEditor(sElementName) {
  if (sElementName === "") {
    return;
  }
  var e = nicEditors.findEditor(sElementName);
  if (e) {
    e.setContent("<br />");
  }
}
function SaveNicEditorData(sElementName) {
  if (sElementName === "") {
    return;
  }
  var e = nicEditors.findEditor(sElementName);
  if (e) {
    e.saveContent();
  }
}
function onLinkDocPWDKeyDown(element, guid, name, image) {
  if (element.keyCode == 13) {
    load_object(guid, "false", "encryptdoc", "", name, image);
    return false;
  }
  return true;
}
function load_object(
  sGUID,
  sHasChild,
  sLinkType,
  sHyper,
  sInName,
  sInImageURL
) {
  load_object2(sGUID, sHasChild, sLinkType, sHyper, sInName, sInImageURL, true);
}
function load_object2(
  sGUID,
  sHasChild,
  sLinkType,
  sHyper,
  sInName,
  sInImageURL,
  bAdd2History
) {
  sGUID = initialise_parameter_str(sGUID, "");
  sHasChild = initialise_parameter_str(sHasChild, "");
  sLinkType = initialise_parameter_str(sLinkType, "");
  sHyper = initialise_parameter_str(sHyper, "");
  sInName = initialise_parameter_str(sInName, "");
  sInImageURL = initialise_parameter_str(sInImageURL, "");
  var sPassword = "";
  var sPayload = "";
  var bExternalURL = false;
  check_browser_visibility();
  if (sGUID === "home") {
    sGUID = "";
  }
  if (sHasChild === "") {
    sHasChild = "false";
  }
  $("#main-busy-loader1").show();
  var sResType = GetResTypeFromGUID(sGUID);
  if (sLinkType == "props" && sHyper == "") {
    sResType = "Element";
  }
  if (sLinkType === "encryptdoc") {
    var ele = $("input[id='linked-document-pwd-field']");
    if (typeof ele !== "undefined") {
      sPassword = ele.val();
    }
    if (sHyper === "") {
      sHyper = sPassword;
    }
  }
  var sText = "";
  var sImageURL = "";
  if (
    sInName == "" &&
    sInImageURL == "" &&
    sGUID != "" &&
    sGUID != "search" &&
    sGUID != "searchresults"
  ) {
    load_diagram_object(sGUID, false);
    return;
  } else {
    sText = sInName;
    sImageURL = sInImageURL;
  }
  sPayload =
    "objectguid=" +
    encodeURIComponent(sGUID) +
    "&haschild=" +
    sHasChild +
    "&linktype=" +
    sLinkType +
    "&objectname=" +
    sText +
    "&hyper=" +
    encodeURIComponent(sHyper) +
    "&imageurl=" +
    encodeURIComponent(sImageURL);
  $.ajax({
    type: "POST",
    cache: false,
    url: "mainview.php",
    data: sPayload,
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (response) {
      if (sLinkType == "composite") {
        var compo = $(
          ".composite-diagram-name",
          "<div>" + response + "</div>"
        ).attr("onclick");
        if (compo !== undefined) {
          eval(compo);
        } else {
          load_object2(
            sGUID,
            sHasChild,
            "",
            sHyper,
            sInName,
            sInImageURL,
            true
          );
        }
      } else {
        $("#main-content-center").html(response);
      }
      load_server_systemoutput();
      if (check_fatal_error("")) {
        if (sGUID === "search") {
          focus_search_term();
        }
        if (bAdd2History) {
          NavigationHistoryAdd(
            sGUID,
            sHasChild,
            sLinkType,
            sHyper,
            sText,
            sImageURL
          );
        } else {
          document.title = get_page_title(sGUID, sText, "", sHyper);
        }
      }
    },
    error: function (jqXHR) {
      if (sGUID === "searchresults") {
        focus_search_term();
        if (jqXHR.status >= 400 && jqXHR.status < 500) {
          var ele = document.getElementById("search-message");
          if (ele) {
            ele.innerHTML = jqXHR.responseText;
            ele.setAttribute("class", "search-message-error");
          } else {
            webea_alert(
              get_translate -
                string("error_while_searching") +
                "\n\t" +
                jqXHR.responseText
            );
          }
        }
        $("#main-busy-loader1").hide();
        $("#miniprops-busy-loader").hide();
      }
    },
    complete: function () {
      if (sGUID === "searchresults" || sGUID === "watchlistresults") {
        load_navbar(sGUID, sHasChild, sLinkType, sHyper, sText, sImageURL);
      }
      $("#main-busy-loader1").hide();
      $("#miniprops-busy-loader").hide();
    },
  });
  if (sGUID !== "searchresults" && sGUID !== "watchlistresults") {
    load_navbar(sGUID, sHasChild, sLinkType, sHyper, sText, sImageURL);
  }
}
function get_page_title(sGUID, sName, sLinkType, sHyper) {
  var sPageNamePrefix = "WebEA - ";
  var sPageName = sPageNamePrefix + "Model Root";
  if (sLinkType !== "") {
    if (sLinkType === "document") {
      sPageName = sPageNamePrefix + "Linked doc for " + sName;
      return sPageName;
    } else if (sLinkType === "encryptdoc") {
      sPageName = sPageNamePrefix + "Encrypted doc " + sName;
      return sPageName;
    }
  }
  if (sGUID === "addmodelroot") {
    $sResType = sPageNamePrefix + "Add Root Node " + sName;
  } else if (sGUID === "addviewpackage") {
    $sResType = sPageNamePrefix + "Add View " + sName;
  } else if (sGUID === "addelement") {
    return sPageNamePrefix + "Add element to " + sName;
  } else if (sGUID === "addelementtest") {
    return sPageNamePrefix + "Add test to " + sName;
  } else if (sGUID === "addelementresalloc") {
    return sPageNamePrefix + "Add resource allocation to " + sName;
  } else if (sGUID === "addelementchgmgmt") {
    var a = sLinkType.split("|");
    var sChgmgtType = a[1];
    if (sChgmgtType === "change") {
      return sPageNamePrefix + "Add change to " + sName;
    } else if (sChgmgtType === "defect") {
      return sPageNamePrefix + "Add defect to " + sName;
    } else if (sChgmgtType === "issue") {
      return sPageNamePrefix + "Add issue to " + sName;
    } else if (sChgmgtType === "task") {
      return sPageNamePrefix + "Add task to " + sName;
    } else if (sChgmgtType === "risk") {
      return sPageNamePrefix + "Add risk to " + sName;
    }
  } else if (sGUID === "editelementnote") {
    return sPageNamePrefix + "Edit note for " + sName;
  } else if (sGUID === "editelementtest") {
    return sPageNamePrefix + "Edit test for " + sName;
  } else if (sGUID === "editelementresalloc") {
    return sPageNamePrefix + "Edit resource allocation for " + sName;
  }
  if (sGUID === "initialize") {
    sPageName = sPageNamePrefix + "HOME";
  } else if (sGUID !== "") {
    sPageName = sPageNamePrefix + "[Unnamed object]";
  }
  if (sName !== "") {
    sPageName = sPageNamePrefix + sName;
  }
  return sPageName;
}
function load_diagram_object(sGUID, bReplaceLastState) {
  var sHasChild = "false";
  var sLinkType = "";
  var sHyper = "";
  $("#main-busy-loader1").show();
  var sResType = GetResTypeFromGUID(sGUID);
  var sURL =
    "./data_api/get_json_element.php" +
    (sGUID != "" ? "?objectguid=" + encodeURIComponent(sGUID) : "");
  $.ajax({
    type: "POST",
    url: sURL,
    dataType: "text json",
    success: function (a) {
      if (a != null) {
        var sObjGUID = "";
        var sObjName = "";
        var sObjType = "";
        var sObjResType = "";
        var sObjLinkType = "";
        var sObjHyper = "";
        var sObjHasChild = "";
        var sObjNType = "";
        var sObjImageURL = "";
        var sFirstDescGUID = "";
        var sFirstDescResType = "";
        var sErrorCode = "";
        var sErrorMsg = "";
        sObjGUID = a["guid"];
        sObjName = a["namealias"];
        sObjType = a["type"];
        sObjResType = a["restype"];
        sObjLinkType = a["linktype"];
        sObjHyper = a["hyper"];
        sObjHasChild = a["haschild"];
        sObjImageURL = a["imageurl"];
        sObjNType = a["ntype"];
        sFirstDescGUID = a["firstdescguid"];
        sCompositeGUID = a["compositeguid"];
        sErrorCode = a["errorcode"];
        sErrorMsg = a["errormsg"];
        if (check_fatal_error(sErrorCode + " - " + sErrorMsg)) {
          if (sObjLinkType !== "") {
            if (sObjLinkType === "Help") {
              sObjHyper = sObjHyper.toLowerCase();
              sObjHyper = gc_sHelpLocation + sObjHyper;
            } else if (sObjLinkType === "Imageman") {
              var iPos = sObjHyper.indexOf("/im_{");
              if (iPos > 0) {
                sObjHyper = sObjHyper.substr(iPos);
                sObjHyper = sObjHyper.replace(/[\/]/g, "");
              }
            }
            load_object_from_hyper(sObjHyper, sObjLinkType);
          } else if (sObjType === "ModelRoot") {
            sLinkType = "";
            sGUID = "mr" + sGUID.substr(2);
            load_object(
              sGUID,
              sHasChild,
              sLinkType,
              sHyper,
              sObjName,
              sObjImageURL
            );
          } else if (
            sObjResType === "Package" ||
            (sObjResType === "Element" && sObjType === "Package")
          ) {
            var nav = $("#webea-navigate-to-diagram").text();
            if (sFirstDescGUID != "" && nav == "1") {
              load_object2(sFirstDescGUID, "false", "", "", "", "", false);
            } else {
              sLinkType = "";
              sGUID = "pk" + sGUID.substr(2);
              load_object(
                sGUID,
                sHasChild,
                sLinkType,
                sHyper,
                sObjName,
                sObjImageURL
              );
            }
          } else if (sObjGUID === "matrix") {
            if (bReplaceLastState) {
              load_object2(
                sObjGUID,
                sHasChild,
                sLinkType,
                sObjHyper,
                sObjName,
                sObjImageURL,
                !bReplaceLastState
              );
              replace_history_state(
                sObjGUID,
                sHasChild,
                sLinkType,
                sObjHyper,
                sObjName,
                sObjImageURL,
                "WebEA",
                "index.php"
              );
            } else {
              load_object(
                sObjGUID,
                sHasChild,
                sLinkType,
                sObjHyper,
                sObjName,
                sObjImageURL
              );
            }
          } else if (sObjNType === "8") {
            var nav = $("#webea-navigate-to-diagram").text();
            if (sCompositeGUID != "" && nav == "1") {
              load_object2(sCompositeGUID, "false", "", "", "", "", false);
            } else {
              if (bReplaceLastState) {
                load_object2(
                  sGUID,
                  sHasChild,
                  sLinkType,
                  sHyper,
                  sObjName,
                  sObjImageURL,
                  !bReplaceLastState
                );
                replace_history_state(
                  sGUID,
                  sHasChild,
                  sLinkType,
                  sHyper,
                  sObjName,
                  sObjImageURL,
                  "WebEA",
                  "index.php"
                );
              } else {
                load_object(
                  sGUID,
                  sHasChild,
                  sLinkType,
                  sHyper,
                  sObjName,
                  sObjImageURL
                );
              }
            }
          } else {
            if (bReplaceLastState) {
              load_object2(
                sGUID,
                sHasChild,
                sLinkType,
                sHyper,
                sObjName,
                sObjImageURL,
                !bReplaceLastState
              );
              replace_history_state(
                sGUID,
                sHasChild,
                sLinkType,
                sHyper,
                sObjName,
                sObjImageURL,
                "WebEA",
                "index.php"
              );
            } else {
              load_object(
                sGUID,
                sHasChild,
                sLinkType,
                sHyper,
                sObjName,
                sObjImageURL
              );
            }
          }
        }
      } else {
        $("#main-busy-loader1").hide();
        $("#miniprops-busy-loader").hide();
      }
    },
    error: function (jqXHR) {
      if (jqXHR.status === 200) {
        var bShowStdMsg = true;
        var sMsg = jqXHR.responseText;
        var iPos = sMsg.indexOf("{");
        if (iPos > 0 && sMsg.substr(sMsg.length - 2, 1) == "}") {
          var sJSONStr = sMsg.substr(iPos);
          if (IsJsonString(sJSONStr)) {
            var o = JSON.parse(sJSONStr);
            var sErrMsg = o["errorcode"] + " - " + o["errormsg"];
            if (check_fatal_error(sErrMsg) == false) {
              bShowStdMsg = false;
            }
          }
        }
        if (bShowStdMsg) {
          webea_alert(
            get_translate_string("unable_to_retrieve_object_details") +
              "\n" +
              sMsg
          );
        }
      } else {
        webea_alert(
          get_translate_string("error_selecting_diagram_object") +
            "\n\t" +
            jqXHR.responseText
        );
      }
      $("#main-busy-loader1").hide();
      $("#miniprops-busy-loader").hide();
    },
  });
}
function IsJsonString(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    Write2Console("IsJSONString:e=" + e.message);
    return false;
  }
  return true;
}
function load_object_from_hyper(sHyper, sLinkType) {
  sHyper = initialise_parameter_str(sHyper, "");
  sLinkType = initialise_parameter_str(sLinkType, "");
  if (sHyper.substr(0, 7) == "http://" || sHyper.substr(0, 8) == "https://") {
    window.open(sHyper, "_self");
    $("#main-busy-loader1").hide();
    $("#miniprops-busy-loader").hide();
  } else if (sLinkType === "Imageman") {
    var sURL =
      "./data_api/dl_model_image.php" +
      (sHyper != "" ? "?objectguid=" + encodeURIComponent(sHyper) : "");
    var ele = document.getElementById("main_diagram_iframe");
    if (ele) {
      ele.src = sURL;
    }
    $("#main-busy-loader1").hide();
    $("#miniprops-busy-loader").hide();
  } else {
    var sName = "";
    var sImageURL = "";
    var sErrorCode = "";
    var sErrorMsg = "";
    var sResType = GetResTypeFromGUID(sHyper);
    var sHasChild = "false";
    if (sResType == "Package" || sResType == "ModelRoot") {
      sHasChild = "true";
    }
    var sURL =
      "./data_api/get_json_element.php" +
      (sHyper != "" ? "?objectguid=" + encodeURIComponent(sHyper) : "");
    $.ajax({
      type: "POST",
      url: sURL,
      dataType: "text json",
      success: function (a) {
        if (a != null) {
          sName = a["namealias"];
          sImageURL = a["imageurl"];
          sErrorCode = a["errorcode"];
          sErrorMsg = a["errormsg"];
        }
        if (check_fatal_error(sErrorCode + " - " + sErrorMsg)) {
          load_object(sHyper, sHasChild, sLinkType, "", sName, sImageURL);
        }
      },
      error: function (jqXHR) {
        if (jqXHR.status === 200) {
          var bShowStdMsg = true;
          var sMsg = jqXHR.responseText;
          var iPos = sMsg.indexOf("{");
          if (iPos > 0 && sMsg.substr(sMsg.length - 2, 1) == "}") {
            var sJSONStr = sMsg.substr(iPos);
            if (IsJsonString(sJSONStr)) {
              var o = JSON.parse(sJSONStr);
              var sErrMsg = o["errorcode"] + " - " + o["errormsg"];
              if (check_fatal_error(sErrMsg) == false) {
                bShowStdMsg = false;
              }
            }
          }
          if (bShowStdMsg) {
            webea_alert("load_object_from_hyper:" + "\t" + sMsg);
          }
        } else {
          webea_alert("load_object_from_hyper:\t" + jqXHR.responseText);
        }
      },
      complete: function () {
        $("#main-busy-loader1").hide();
        $("#miniprops-busy-loader").hide();
      },
    });
  }
}
function refresh_current() {
  if (typeof window.history !== "undefined") {
    if (
      window.history.state !== null &&
      typeof window.history.state !== "undefined"
    ) {
      var sGUID = window.history.state.guid;
      var sHasChild = window.history.state.haschild;
      var sLinkType = window.history.state.linktype;
      var sHyper = window.history.state.hyper;
      var sName = window.history.state.name;
      var sImageURL = window.history.state.imageurl;
      load_object2(
        sGUID,
        sHasChild,
        sLinkType,
        sHyper,
        sName,
        sImageURL,
        false
      );
    } else {
      var sGUID = g_sLastGUID;
      var sHasChild = g_sLastHasChild;
      var sLinkType = g_sLastLinkType;
      var sHyper = g_sLastHyper;
      var sName = g_sLastName;
      var sImageURL = g_sLastImageURL;
      load_object2(
        sGUID,
        sHasChild,
        sLinkType,
        sHyper,
        sName,
        sImageURL,
        false
      );
    }
  }
}
function load_home(bReplaceHistory) {
  check_browser_visibility();
  var bSomethingLoaded = false;
  try {
    bValid = false;
    var sDefaultDiagram = $("#model-default-diagram").text();
    if (sDefaultDiagram !== "") {
      if (
        sDefaultDiagram.substr(0, 4) == "dg_{" &&
        sDefaultDiagram.length == 41
      ) {
        bValid = true;
      } else if (
        sDefaultDiagram.substr(0, 1) == "{" &&
        sDefaultDiagram.substr(-1) == "}" &&
        sDefaultDiagram.length == 38
      ) {
        bValid = true;
        sDefaultDiagram = "dg_" + sDefaultDiagram;
      }
      if (bValid) {
        if (bReplaceHistory) {
          replace_history_state(
            "",
            "",
            "",
            "",
            "",
            "",
            "WebEA - Model Root",
            "index.php"
          );
        }
        load_diagram_object(sDefaultDiagram, false);
        bSomethingLoaded = true;
      }
    }
    if (!bSomethingLoaded) {
      if (bReplaceHistory) {
        load_object2("", "false", "", "", "", "", false);
        replace_history_state(
          "",
          "",
          "",
          "",
          "",
          "",
          "WebEA - Model Root",
          "index.php"
        );
      } else {
        load_object("", "false", "", "", "", "");
      }
      bSomethingLoaded = true;
    }
  } catch (err) {
    webea_alert(
      get_translate_string("error_navigating_to_home") + " " + err.message
    );
  }
  return bSomethingLoaded;
}
function load_miniprops_object(sGUID) {
  $("#miniprops-busy-loader").show();
  if ($("#main-mini-properties-view").css("display") === "block") {
    var ele = $("#mini-properties-view-section");
    if (ele.length) {
      var bLoadProps = false;
      var sMPNavigates = $("#webea-miniprops-navigates").text();
      if (sMPNavigates == "1") {
        var sURL =
          "./data_api/get_json_element.php" +
          (sGUID != "" ? "?objectguid=" + encodeURIComponent(sGUID) : "");
        $.ajax({
          type: "POST",
          url: sURL,
          dataType: "text json",
          success: function (a) {
            if (a != null) {
              var sObjGUID = "";
              var sObjName = "";
              var sObjType = "";
              var sObjResType = "";
              var sObjLinkType = "";
              var sObjHyper = "";
              var sObjHasChild = "";
              var sObjImageURL = "";
              var sObjNType = "";
              var sFirstDescGUID = "";
              var sCompositeGUID = "";
              var sErrorCode = "";
              var sErrorMsg = "";
              sObjGUID = a["guid"];
              sObjName = a["namealias"];
              sObjType = a["type"];
              sObjResType = a["restype"];
              sObjLinkType = a["linktype"];
              sObjHyper = a["hyper"];
              sObjHasChild = a["haschild"];
              sObjImageURL = a["imageurl"];
              sObjNType = a["ntype"];
              sFirstDescGUID = a["firstdescguid"];
              sCompositeGUID = a["compositeguid"];
              sErrorCode = a["errorcode"];
              sErrorMsg = a["errormsg"];
              if (check_fatal_error(sErrorCode + " - " + sErrorMsg)) {
                if (sObjLinkType !== "") {
                  if (sObjLinkType === "Help") {
                    sObjHyper = sObjHyper.toLowerCase();
                    sObjHyper = gc_sHelpLocation + sObjHyper;
                  } else if (sObjLinkType === "Imageman") {
                    var iPos = sObjHyper.indexOf("/im_{");
                    if (iPos > 0) {
                      sObjHyper = sObjHyper.substr(iPos);
                      sObjHyper = sObjHyper.replace(/[\/]/g, "");
                    }
                  }
                  load_object_from_hyper(sObjHyper, sObjLinkType);
                } else if (sObjType === "ModelRoot") {
                  sGUID = "mr" + sGUID.substr(2);
                  load_miniprops_object_internal(sGUID);
                } else if (
                  sObjResType === "Package" ||
                  (sObjResType === "Element" && sObjType === "Package")
                ) {
                  var nav = $("#webea-navigate-to-diagram").text();
                  if (sFirstDescGUID != "" && nav == "1") {
                    load_object2(
                      sFirstDescGUID,
                      "false",
                      "",
                      "",
                      "",
                      "",
                      false
                    );
                  } else {
                    sGUID = "pk" + sGUID.substr(2);
                    load_miniprops_object_internal(sGUID);
                  }
                } else if (sObjGUID === "matrix") {
                  load_object(
                    sObjGUID,
                    "false",
                    "",
                    sObjHyper,
                    sObjName,
                    sObjImageURL
                  );
                } else if (sObjNType === "8") {
                  var nav = $("#webea-navigate-to-diagram").text();
                  if (sCompositeGUID != "" && nav == "1") {
                    load_object2(
                      sCompositeGUID,
                      "false",
                      "",
                      "",
                      "",
                      "",
                      false
                    );
                  } else {
                    load_miniprops_object_internal(sGUID);
                  }
                } else {
                  load_miniprops_object_internal(sGUID);
                }
              }
            } else {
            }
          },
          error: function (jqXHR) {
            if (jqXHR.status === 200) {
              var bShowStdMsg = true;
              var sMsg = jqXHR.responseText;
              var iPos = sMsg.indexOf("{");
              if (iPos > 0 && sMsg.substr(sMsg.length - 2, 1) == "}") {
                var sJSONStr = sMsg.substr(iPos);
                if (IsJsonString(sJSONStr)) {
                  var o = JSON.parse(sJSONStr);
                  var sErrMsg = o["errorcode"] + " - " + o["errormsg"];
                  if (check_fatal_error(sErrMsg) == false) {
                    bShowStdMsg = false;
                  }
                }
              }
              if (bShowStdMsg) {
                webea_alert(
                  get_translate_string("unable_to_retrieve_object_details") +
                    "\n" +
                    sMsg
                );
              }
            } else {
              webea_alert(
                get_translate_string("error_selecting_diagram_object") +
                  "\n\t" +
                  jqXHR.responseText
              );
            }
          },
          complete: function () {},
        });
      } else {
        load_miniprops_object_internal(sGUID);
      }
    }
  } else {
    sError = get_translate_string("miniprops_is_disabled");
    $("#hamburger-pnl-miniprops").removeClass("mainsprite-tick");
    $.get(
      "./data_api/set_setting.php?varname=show_miniproperties&varval=" +
        "false",
      function (data) {}
    );
    load_diagram_object(sGUID);
  }
}
function load_miniprops_object_internal(sGUID) {
  if (sGUID !== "") {
    $.ajax({
      type: "POST",
      cache: false,
      url: "miniproperties.php",
      data: { objectguid: sGUID },
      beforeSend: function () {
        $("#miniprops-busy-loader").show();
      },
      success: function (sHTML) {
        document.getElementById("mini-properties-view-section").innerHTML =
          sHTML;
        load_server_systemoutput();
      },
      error: function (jqXHR) {
        Write2Console("load_miniprops_object:" + jqXHR.statusText);
      },
      complete: function () {
        $("#miniprops-busy-loader").hide();
      },
    });
  }
}
function GetResTypeFromGUID(sGUID) {
  var sResType = "";
  sGUID = String(sGUID);
  if (sGUID.substr(0, 4) == "mr_{") {
    sResType = "ModelRoot";
  } else if (sGUID.substr(0, 4) == "pk_{") {
    sResType = "Package";
  } else if (sGUID.substr(0, 4) == "dg_{" || sGUID.substr(0, 4) == "di_{") {
    sResType = "Diagram";
  } else if (sGUID.substr(0, 4) == "el_{") {
    sResType = "Element";
  } else if (sGUID === "search") {
    sResType = "Search";
  } else if (sGUID === "searchresults") {
    sResType = "Search Results";
  } else if (sGUID === "watchlist") {
    sResType = "Watchlist";
  } else if (sGUID === "watchlistconfig") {
    sResType = "Watchlist configuration";
  } else if (sGUID === "watchlistresults") {
    sResType = "Watchlist Results";
  }
  return sResType;
}
function NavigationHistoryAdd(
  sGUID,
  sHasChild,
  sLinkType,
  sHyper,
  sName,
  sImageURL
) {
  var sPrevGUID = "";
  var sPrevHasChild = "";
  var sPrevLinkType = "";
  if (sGUID === "") {
    sGUID = "home";
    sImageURL = "images/navbar/root.png";
  }
  var a = {};
  a["guid"] = sGUID;
  a["haschild"] = sHasChild;
  a["linktype"] = sLinkType;
  a["hyper"] = sHyper;
  a["name"] = sName;
  a["imageurl"] = sImageURL;
  var sDisplayName = get_page_title(sGUID, sName, sLinkType, sHyper);
  document.title = sDisplayName;
  if (typeof history.pushState != "undefined") {
    window.history.pushState(a, sDisplayName, "index.php");
  }
  g_sLastGUID = sGUID;
  g_sLastHasChild = sHasChild;
  g_sLastLinkType = sLinkType;
  g_sLastHyper = sHyper;
  g_sLastName = sName;
  g_sLastImageURL = sImageURL;
}
function OnIndexPopState(event) {
  if (event.state !== null) {
    var sGUID = event.state.guid;
    var sHasChild = event.state.haschild;
    var sLinkType = event.state.linktype;
    var sHyper = event.state.hyper;
    var sName = event.state.name;
    var sImageURL = event.state.imageurl;
    load_object2(sGUID, sHasChild, sLinkType, sHyper, sName, sImageURL, false);
  } else {
    Write2Console(
      "onIndexPopState:Count=" +
        window.history.length +
        " something wrong with event.state"
    );
  }
}
function MoveToPrevItemInNavigationHistory() {
  if (
    typeof window.history.state !== "undefined" &&
    window.history.state !== null
  ) {
    window.history.back();
  } else {
    if (
      g_sLastGUID === "editelementnote" ||
      g_sLastGUID === "editelementtest" ||
      g_sLastGUID === "editelementresalloc"
    ) {
      var a = g_sLastLinkType.split("|");
      var sGUID = "";
      if (a.length > 0) {
        sGUID = a[0];
      }
      if (sGUID !== "") {
        load_object2(sGUID, "", "", "", g_sLastName, g_sLastImageURL, false);
      }
    } else if (
      g_sLastGUID === "watchlistconfig" ||
      g_sLastGUID === "watchlistresults"
    ) {
      load_object2(
        "watchlist",
        "",
        "",
        "",
        "Watchlist",
        "images/element16/watchlist.png",
        false
      );
    } else if (g_sLastGUID === "watchlist") {
      load_home(false);
    } else {
      Write2Console(
        "MoveToPrevItemInNavigationHistory:LastGUID=" + g_sLastGUID
      );
      window.history.back();
    }
  }
}
function get_current_history_state() {
  a = {};
  if (
    typeof window.history.state !== "undefined" &&
    window.history.state !== null
  ) {
    if (typeof window.history.state.guid !== "undefined") {
      a["guid"] = window.history.state.guid;
      a["haschild"] = window.history.state.haschild;
      a["linktype"] = window.history.state.linktype;
      a["hyper"] = window.history.state.hyper;
      a["name"] = window.history.state.name;
      a["imageurl"] = window.history.state.imageurl;
    } else {
      Write2Console("get_current_history_state:state.guid is undefined");
    }
  }
  return JSON.stringify(a);
}
function replace_history_state(
  sGUID,
  sHasChild,
  sLinkType,
  sHyper,
  sName,
  sImageURL,
  sTitle,
  sPage
) {
  if (typeof history.replaceState != "undefined") {
    window.history.replaceState(
      {
        guid: sGUID,
        haschild: sHasChild,
        linktype: sLinkType,
        hyper: sHyper,
        name: sName,
        imageurl: sImageURL,
      },
      sTitle,
      sPage
    );
  }
}
function OnStoreLastPage(event) {
  if (!g_bIsLoggingOff) {
    var sSessionID = get_cookie_value("webea_session");
    var sCurrPage = get_current_history_state();
    if (sCurrPage != "") {
      var sLastPageTimeout = $("#model-lastpage-timeout").text();
      var iLastPageTimeout = Number(sLastPageTimeout);
      var date = new Date();
      date.setTime(date.getTime() + iLastPageTimeout * 60000);
      var sExpires = ";expires=" + date.toUTCString();
      var sPageModelNo = $("#page-built-with-model-no").text();
      document.cookie =
        "webea_lastpage_" +
        sPageModelNo +
        "=" +
        sCurrPage +
        sExpires +
        ";path=/;";
    }
  }
}
function get_cookie_value(sName) {
  var b = document.cookie.match("(^|;)\\s*" + sName + "\\s*=\\s*([^;]+)");
  return b ? b.pop() : "";
}
function Write2Console(sMessage) {
  if (g_bJSDebugging) {
    if (typeof window.console !== "undefined") {
      if (window.console) {
        window.console.log(sMessage);
      }
    }
  }
}
function set_property_layout(sPropLayout) {
  if (sPropLayout != "1" && sPropLayout != "2") {
    sPropLayout = "2";
  }
  $.get(
    "./data_api/set_setting.php?varname=propertylayout&varval=" + sPropLayout,
    function (data) {
      if (sPropLayout == "2") {
        $("#prop-layout-split").addClass("prop-layout-split-enabled");
        $("#prop-layout-split").removeClass("prop-layout-split-disabled");
        $("#navbar-prop-layout-split").addClass("prop-layout-split-enabled");
        $("#navbar-prop-layout-split").removeClass(
          "prop-layout-split-disabled"
        );
        $("#prop-layout-wide").addClass("prop-layout-wide-disabled");
        $("#prop-layout-wide").removeClass("prop-layout-wide-enabled");
        $("#navbar-prop-layout-wide").addClass("prop-layout-wide-disabled");
        $("#navbar-prop-layout-wide").removeClass("prop-layout-wide-enabled");
        if (document.getElementById("properties-main") != null) {
          document
            .getElementById("properties-main")
            .setAttribute("class", "properties-main2");
          document
            .getElementById("properties-right")
            .setAttribute("class", "properties-right2");
        }
      } else {
        $("#prop-layout-wide").addClass("prop-layout-wide-enabled");
        $("#prop-layout-wide").removeClass("prop-layout-wide-disabled");
        $("#navbar-prop-layout-wide").addClass("prop-layout-wide-enabled");
        $("#navbar-prop-layout-wide").removeClass("prop-layout-wide-disabled");
        $("#prop-layout-split").addClass("prop-layout-split-disabled");
        $("#prop-layout-split").removeClass("prop-layout-split-enabled");
        $("#navbar-prop-layout-split").addClass("prop-layout-split-disabled");
        $("#navbar-prop-layout-split").removeClass("prop-layout-split-enabled");
        if (document.getElementById("properties-main") != null) {
          document
            .getElementById("properties-main")
            .setAttribute("class", "properties-main1");
          document
            .getElementById("properties-right")
            .setAttribute("class", "properties-right1");
        }
      }
      $("#navbar-hamburger-menu").hide();
    }
  );
}
function set_main_layout(sMainLayout) {
  if (sMainLayout !== "1" && sMainLayout !== "2" && sMainLayout !== "3") {
    sMainLayout = "1";
  }
  var bNeedsRefresh = false;
  $.get(
    "./data_api/set_setting.php?varname=mainlayout&varval=" + sMainLayout,
    function (data) {
      if (sMainLayout == "1") {
        $("#main-layout-icons").addClass("hamburger-radio-icon");
        $("#main-layout-list").removeClass("hamburger-radio-icon");
        $("#main-layout-notes").removeClass("hamburger-radio-icon");
        bNeedsRefresh = true;
      } else if (sMainLayout == "2") {
        $("#main-layout-icons").removeClass("hamburger-radio-icon");
        $("#main-layout-list").addClass("hamburger-radio-icon");
        $("#main-layout-notes").removeClass("hamburger-radio-icon");
        bNeedsRefresh = true;
      } else if (sMainLayout == "3") {
        $("#main-layout-icons").removeClass("hamburger-radio-icon");
        $("#main-layout-list").removeClass("hamburger-radio-icon");
        $("#main-layout-notes").addClass("hamburger-radio-icon");
        bNeedsRefresh = true;
      }
      if (bNeedsRefresh) {
        var sViewingMode = $("#webea-viewing-mode").text();
        if (sViewingMode == "1") {
          refresh_current();
        }
      }
    }
  );
}
function OnMenuAccordion(sMenuSectionName) {
  var sCSV = "contextmenu-package,contextmenu-diagram,contextmenu-properties";
  var a = sCSV.split(",");
  if (a.length > 0) {
    for (var i = 0; i < a.length; i++) {
      var ele = document.getElementById(a[i]);
      if (ele != null) {
        ele.className = ele.className.replace("w3-show", "w3-hide");
      }
    }
  }
  if (sMenuSectionName == "") {
    sMenuSectionName == "contextmenu-package";
  }
  var ele = document.getElementById(sMenuSectionName);
  if (ele != null) {
    ele.className = ele.className.replace("w3-hide", "w3-show");
  }
}
function OnPropAccordion(sPropSectionName) {
  var x = document.getElementById(sPropSectionName);
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
    x.previousElementSibling.className =
      x.previousElementSibling.className.replace(
        "section-heading-closed",
        "section-heading-open"
      );
    x.previousElementSibling.className =
      x.previousElementSibling.className.replace(
        "section-heading-miniprops-closed",
        "section-heading-miniprops-open"
      );
    x.previousElementSibling.children[0].className =
      x.previousElementSibling.children[0].className.replace(
        "section-heading-closed",
        "section-heading-open"
      );
    $.get(
      "./data_api/set_setting.php?varname=prop_expanded_" +
        sPropSectionName +
        "&varval=1",
      function (data) {}
    );
  } else {
    x.className = x.className.replace(" w3-show", "");
    x.previousElementSibling.className =
      x.previousElementSibling.className.replace(
        "section-heading-open",
        "section-heading-closed"
      );
    x.previousElementSibling.className =
      x.previousElementSibling.className.replace(
        "section-heading-miniprops-open",
        "section-heading-miniprops-closed"
      );
    x.previousElementSibling.children[0].className =
      x.previousElementSibling.children[0].className.replace(
        "section-heading-open",
        "section-heading-closed"
      );
    $.get(
      "./data_api/set_setting.php?varname=prop_expanded_" +
        sPropSectionName +
        "&varval=0",
      function (data) {}
    );
  }
}
function OnFormRunAddDiscussion(event) {
  event.preventDefault();
  var sComments =
    document.getElementById("discussion-form1").elements["comments"].value;
  if (sComments !== "") {
    var sGUID =
      document.getElementById("discussion-form1").elements["guid"].value;
    var sPayload = $("#discussion-form1").serializeAndEncode();
    $.ajax({
      type: "POST",
      url: "./data_api/add_discussion.php",
      data: sPayload,
      beforeSend: function () {
        $("#main-busy-loader1").show();
      },
      success: function (sErrorMsg) {
        sErrorMsg = str_trim(sErrorMsg);
        if (sErrorMsg == "") {
          refresh_discussion_section(sGUID, "");
        } else {
          if (check_fatal_error(sErrorMsg)) {
            webea_alert("Error:" + sErrorMsg);
          }
        }
      },
      error: function (jqXHR) {
        $("#main-busy-loader1").hide();
        webea_alert("OnFormRunAddDiscussion:" + jqXHR.responseText);
      },
      complete: function () {
        $("#main-busy-loader1").hide();
        $("#miniprops-busy-loader").hide();
      },
    });
  }
}
function refresh_discussion_section(sElementGUID, sDiscussGUID2Expand) {
  usealtcolour = 0;
  if ($(".section-heading-open").hasClass("section-heading-miniprops-open")) {
    usealtcolour = 1;
  }
  sPayload =
    "objectguid=" +
    encodeURIComponent(sElementGUID) +
    "&usealtcolour=" +
    usealtcolour;
  $.ajax({
    type: "POST",
    cache: false,
    url: "./refreshdiscussions.php",
    data: sPayload,
    success: function (response) {
      var sErrorMsg = str_trim(response);
      if (sErrorMsg.substr(0, 6) == "error:") {
        sErrorMsg = sErrorMsg.substr(7);
      } else {
        sErrorMsg = "";
      }
      if (check_fatal_error(sErrorMsg)) {
        document.getElementById("discussion-section").outerHTML = response;
        load_server_systemoutput();
        var ele = document.getElementById("replies_" + sDiscussGUID2Expand);
        if (ele) {
          par = ele.parentNode;
          if (ele.style.display === "none" || ele.style.display === "") {
            ele.style.display = "block";
            par.className = "discussion-item collapsible-section-header-opened";
          }
        }
      }
    },
    error: function (jqXHR) {
      webea_alert("refresh_discussion_section:" + jqXHR.responseText);
    },
  });
}
function OnFormRunAddReply(e, oForm) {
  e.preventDefault();
  var sPayload = $(oForm).serializeAndEncode();
  var sComments = oForm.comments.value;
  if (sComments !== "") {
    var sIsReply = "true";
    var sObjectGUID = oForm.objectguid.value;
    var sGUID = oForm.guid.value;
    $.ajax({
      type: "POST",
      url: "./data_api/add_discussion.php",
      data: sPayload,
      beforeSend: function () {
        $("#main-busy-loader1").show();
      },
      success: function (sErrorMsg) {
        sErrorMsg = str_trim(sErrorMsg);
        if (sErrorMsg == "") {
          refresh_discussion_section(sObjectGUID, sGUID);
        } else {
          if (check_fatal_error(sErrorMsg)) {
            webea_alert("Error:" + sErrorMsg);
          }
        }
      },
      error: function (jqXHR) {
        $("#main-busy-loader1").hide();
        $("#miniprops-busy-loader").hide();
        webea_alert("OnFormRunAddReply:" + jqXHR.responseText);
      },
      complete: function () {
        $("#main-busy-loader1").hide();
        $("#miniprops-busy-loader").hide();
      },
    });
  }
}
function OnToggleDiscussionReplies(ele) {
  var ele = $(ele).next().next().get(0);
  if (ele != null) {
    var par = ele.parentNode;
    var eleImg = null;
    if (par != null) {
      eleImg = par.getElementsByTagName("img");
    }
    var previousDisplay = ele.style.display;
    $(function () {
      $(".discussion-item-replies").hide();
      $(".discussion-item.collapsible-section-header-opened")
        .removeClass("discussion-item collapsible-section-header-opened")
        .addClass("discussion-item collapsible-section-header-closed");
      $(".discussion-item-icon.collapsible-section-header-opened-icon")
        .removeClass("collapsible-section-header-opened-icon")
        .addClass("collapsible-section-header-closed-icon");
    });
    if (previousDisplay === "none" || previousDisplay === "") {
      ele.style.display = "block";
      par.className = "discussion-item collapsible-section-header-opened";
      if (eleImg != null) {
        eleImg[0].className =
          "discussion-item-icon collapsible-section-header-opened-icon show-cursor-pointer";
      }
    }
  }
}
function OnTogglePropertiesReviewDiscussionReplies(sGUID) {
  var ele = document.getElementById("replies_" + sGUID);
  if (ele != null) {
    par = ele.parentNode;
    var eleImg = null;
    if (par != null) {
      eleImg = par.getElementsByTagName("img");
    }
    var previousDisplay = ele.style.display;
    $(function () {
      $(".review-item-replies").hide();
      $(".review-item.collapsible-section-header-opened")
        .removeClass("review-item collapsible-section-header-opened")
        .addClass("review-item collapsible-section-header-closed");
      $(".review-item-icon.collapsible-section-header-opened-icon")
        .removeClass("collapsible-section-header-opened-icon")
        .addClass("collapsible-section-header-closed-icon");
    });
    if (previousDisplay === "none" || previousDisplay === "") {
      ele.style.display = "block";
      par.className = "review-item collapsible-section-header-opened";
      if (eleImg != null) {
        eleImg[0].className =
          "review-item-icon collapsible-section-header-opened-icon show-cursor-pointer";
      }
    }
  }
}
function OnToggleReviewDiscussionReplies(sGUID) {
  var ele = document.getElementById("mpreplies_" + sGUID);
  if (ele != null) {
    par = ele.parentNode;
    var eleImg = null;
    if (par != null) {
      eleImg = par.getElementsByTagName("img");
    }
    var previousDisplay = ele.style.display;
    if (previousDisplay === "none" || previousDisplay === "") {
      ele.style.display = "block";
      par.className =
        "reviewdiscussion-discussion-item collapsible-section-header-opened";
      if (eleImg != null) {
        eleImg[0].className =
          "reviewdiscussion-discussion-item-icon collapsible-section-header-opened-icon show-cursor-pointer";
      }
    } else {
      ele.style.display = "none";
      par.className =
        "reviewdiscussion-discussion-item collapsible-section-header-closed";
      if (eleImg != null) {
        eleImg[0].className =
          "reviewdiscussion-discussion-item-icon collapsible-section-header-closed-icon show-cursor-pointer";
      }
    }
  }
}
function OnToggleCollapsibleSection(ele) {
  if (ele != null) {
    eleImg = $(ele).children().first();
    if (ele.className.indexOf("collapsible-section-header-closed") == -1) {
      ele.className = ele.className.replace(
        "collapsible-section-header-opened",
        "collapsible-section-header-closed"
      );
      if (eleImg != null) {
        eleImg[0].className = "collapsible-section-header-closed-icon";
      }
      var ele2 = ele.nextElementSibling;
      if (ele2 != null) {
        ele2.className = ele2.className.replace("w3-show", "w3-hide");
      }
    } else {
      ele.className = ele.className.replace(
        "collapsible-section-header-closed",
        "collapsible-section-header-opened"
      );
      if (eleImg != null) {
        eleImg[0].className = "collapsible-section-header-opened-icon";
      }
      var ele2 = ele.nextElementSibling;
      if (ele2 != null) {
        ele2.className = ele2.className.replace("w3-hide", "w3-show");
      }
    }
  }
}
function OnTogglePlusMinusState(eleParent, sChildElementID) {
  var ele = document.getElementById(sChildElementID);
  if (ele.style.display === "none" || ele.style.display === "") {
    ele.style.display = "block";
    if (eleParent) {
      eleParent.className = eleParent.className.replace(
        "collapsible-plusminussection-closed",
        "collapsible-plusminussection-opened"
      );
    }
  } else {
    ele.style.display = "none";
    if (eleParent) {
      eleParent.className = eleParent.className.replace(
        "collapsible-plusminussection-opened",
        "collapsible-plusminussection-closed"
      );
    }
  }
}
function EnsureInputFieldVisible(element) {
  if (element != null) {
    setTimeout(function () {
      element.scrollIntoView();
    }, 500);
  }
}
function OnShowAboutPage(sWebEAVersion) {
  $("#navbar-hamburger-menu").hide();
  var sMessage = "";
  var sURL = "./data_api/get_json_serverdetails.php";
  $.ajax({
    type: "POST",
    url: sURL,
    dataType: "text json",
    beforeSend: function () {
      $("#main-busy-loader1").show();
    },
    success: function (a) {
      if (a != null) {
        var sDBMan = "";
        var sAuthor = "";
        var sAuthorWeb = "";
        var sPCSVersion = "";
        var sVersion = "";
        var sLicense = "";
        var sProtocol = "";
        var sServer = "";
        var sPort = "";
        var sDBAlias = "";
        var sReadOnly = "";
        var sSecurityEnabled = "";
        var sLoginUser = "";
        var sLoginFullName = "";
        var sFormattedUser = "";
        sDBMan = a["databasemanager"];
        sAuthor = a["appauthor"];
        sAuthorWeb = a["appauthorwebsite"];
        sPCSVersion = a["pcsversion"];
        sVersion = a["version"];
        sLicense = a["license"];
        sLicenseExpiry = a["licenseexpiry"];
        sProtocol = a["protocol"];
        sServer = a["server"];
        sPort = a["port"];
        sDBAlias = a["dbalias"];
        sReadOnly = a["readonly"];
        sSecurityEnabled = a["securityenabled"];
        sLoginUser = a["loginuser"];
        sLoginFullName = a["loginfullname"];
        sReviewSession = a["reviewsession"];
        sReviewSessionName = a["reviewsessionname"];
        if (sLoginUser !== "" && sLoginFullName !== "") {
          sFormattedUser = sLoginFullName + " (" + sLoginUser + ")";
        } else if (sLoginUser !== "") {
          sFormattedUser = sLoginUser;
        } else if (sLoginFullName !== "") {
          sFormattedUser += sLoginFullName;
        }
        var sFullServer = "";
        sFullServer = (sProtocol ? sProtocol : "localhost") + "://";
        sFullServer = sFullServer + (sServer ? sServer : "localhost");
        sFullServer = sFullServer + ":" + (sPort ? sPort : "80");
        SetupWebEAPropDivElement("author", sAuthor);
        SetupWebEAPropDivElement("pcsversion", sPCSVersion);
        SetupWebEAPropDivElement("sscsversion", sVersion);
        SetupWebEAPropDivElement("sscslicense", sLicense);
        SetupWebEAPropDivElement("sscslicenseexpiry", sLicenseExpiry);
        SetupWebEAPropDivElement("server", sFullServer);
        SetupWebEAPropDivElement("readonly", sReadOnly);
        SetupWebEAPropDivElement("appversion", "WebEA v" + sWebEAVersion);
        SetupWebEAPropDivElement("security", sSecurityEnabled);
        SetupWebEAPropDivElement("user", sFormattedUser);
        SetupWebEAPropDivElement("review", sReviewSessionName);
        $("#main-page-overlay").show();
        $("#webea-about-dialog").show();
      }
    },
    error: function (jqXHR) {
      if (jqXHR.status === 200) {
        webea_alert(get_translate_string("unable_to_retrieve_server_details"));
      } else {
        webea_alert(
          get_translate_string("error_retrieving_server_details") +
            "\n\t" +
            jqXHR.responseText
        );
      }
    },
    complete: function () {
      $("#main-busy-loader1").hide();
      $("#miniprops-busy-loader").hide();
    },
  });
}
function SetupWebEAPropDivElement(sEleName, sValue) {
  if (sValue === "") {
    $("#webea-about-line-" + sEleName).hide();
    $("#webea-about-line-value-" + sEleName).val("");
  } else {
    $("#webea-about-line-" + sEleName).show();
    $("#webea-about-line-value-" + sEleName).text(sValue);
  }
}
function OnClickWatchlistItem(sClause, sInName) {
  load_object(
    "watchlistresults",
    "false",
    "",
    sClause,
    sInName,
    "images/element16/watchlistresults.png"
  );
}
function OnLoad_SetupSpecialCtrls(sRichEditCtrlsCSV, sDatePickerCtrlsCSV) {
  OnLoad_SetupRichEditCtrls(sRichEditCtrlsCSV);
  OnLoad_DatePickerCtrls(sDatePickerCtrlsCSV);
  OnLoad_ConvertPasteToPlainText();
}
function OnLoad_SetupRichEditCtrls(sCtrlsCSV) {
  if (sCtrlsCSV != "") {
    var a = sCtrlsCSV.split(",");
    if (a.length > 0) {
      for (var i = 0; i < a.length; i++) {
        var ele = document.getElementById(a[i]);
        if (ele != null) {
          new nicEditor({
            buttonList: [
              "bold",
              "italic",
              "underline",
              "ol",
              "ul",
              "superscript",
              "subscript",
              "forecolor",
              "html",
            ],
            maxHeight: 300,
            minwidth: 300,
          }).panelInstance(a[i]);
          $(".nicEdit-panelContain").parent().width("100%");
          $(".nicEdit-panelContain").parent().next().width("100%");
        }
      }
    }
  }
}
function OnLoad_ConvertPasteToPlainText() {
  $(".nicEdit-main").on("paste", function (e) {
    e.preventDefault();
    var text;
    var clp = (e.originalEvent || e).clipboardData;
    if (clp === undefined || clp === null) {
      text = window.clipboardData.getData("text") || "";
      if (text !== "") {
        text = text.replace(/(?:\r\n|\r|\n)/g, "<br />");
        if (window.getSelection) {
          var newNode = document.createElement("span");
          newNode.innerHTML = text;
          window.getSelection().getRangeAt(0).insertNode(newNode);
        } else {
          document.selection.createRange().pasteHTML(text);
        }
      }
    } else {
      text = clp.getData("text/plain") || "";
      if (text !== "") {
        document.execCommand("insertText", false, text);
      }
    }
  });
}
function OnLoad_DatePickerCtrls(sCtrlsCSV) {
  if (sCtrlsCSV != "") {
    var a = sCtrlsCSV.split(",");
    if (a.length > 0) {
      for (var i = 0; i < a.length; i++) {
        if (a[i] != "") {
          var a2 = a[i].split("|");
          var sTextField = a2[0];
          var sImage = a2[1];
          $("#" + sTextField).datepick({
            showOnFocus: false,
            showTrigger: "#" + sImage,
            dateFormat: "yyyy-mm-dd",
            showAnim: "",
            showSpeed: 10,
          });
        }
      }
    }
  }
}
function OnJoinLeaveReviewSession(
  sReviewElementGUID,
  sReviewElementName,
  sCurrObjectGUID
) {
  $.get(
    "./data_api/set_setting.php?varname=review_session&varval=" +
      sReviewElementGUID +
      "&varval2=" +
      sReviewElementName,
    function (data) {
      if (sReviewElementGUID !== "") {
        document
          .getElementById("review-session-join-button")
          .setAttribute("disabled", "");
        document
          .getElementById("review-session-leave-button")
          .removeAttribute("disabled");
      } else {
        document
          .getElementById("review-session-join-button")
          .removeAttribute("disabled");
        document
          .getElementById("review-session-leave-button")
          .setAttribute("disabled", "");
      }
      if (sCurrObjectGUID !== "") {
        if ($("#discussion-section").css("display") === "block") {
          refresh_discussion_section(sCurrObjectGUID, "");
        } else {
          refresh_current();
        }
      }
      var sPayload =
        "reviewguid=" +
        encodeURIComponent(sReviewElementGUID) +
        "&reviewname=" +
        encodeURIComponent(sReviewElementName);
      $.ajax({
        type: "POST",
        cache: false,
        url: "statusbar.php",
        data: sPayload,
        success: function (sHTML) {
          document.getElementById("main-statusbar").innerHTML = sHTML;
        },
        error: function (jqXHR) {
          webea_alert("OnJoinLeaveReviewSession:" + jqXHR.responseText);
        },
      });
    }
  );
}
function OnRequestDiagramRegenerate(sGUID) {
  if (sGUID !== "") {
    var sPayload;
    sPayload = "objectguid=" + sGUID;
    $.ajax({
      type: "POST",
      cache: false,
      url: "./data_api/request_diagramgenerate.php",
      data: sPayload,
      success: function (sErrorMsg) {
        sErrorMsg = str_trim(sErrorMsg);
        if (sErrorMsg === "") {
          document
            .getElementById("generatediagram-action-button")
            .setAttribute("disabled", "");
          document
            .getElementById("diagram-regeneration-label-line")
            .removeAttribute("style");
        } else {
          if (check_fatal_error(sErrorMsg)) {
            webea_alert("OnRequestDiagramRegenerate:" + sErrorMsg);
          }
        }
      },
      error: function (jqXHR) {
        webea_alert("OnRequestDiagramRegenerate:" + jqXHR.responseText);
      },
    });
  }
}
function OnShowCurrentLink(sLink, sFullURL) {
  if (sLink !== null && sLink !== "") {
    $("#webea-show-link-textarea").val(sLink);
    $("#webea-show-fulllink-textarea").val(sFullURL);
    $("#main-page-overlay").show();
    $("#webea-show-link-dialog").show();
  }
}
function OnPromptForGotoGUID() {
  $("#webea-goto-link-textarea").val("");
  $("#main-page-overlay").show();
  $("#webea-goto-link-dialog").show();
  $("#webea-goto-link-textarea").focus();
  $("#navbar-search-menu").hide();
}
function OnGotoGUIDTextKeypress(event) {
  if (event) {
    if (event.keyCode === 13) {
      OnWebEAGotoGUID();
      return false;
    }
  }
  return true;
}
function OnWebEAGotoGUID() {
  var sLink = $("#webea-goto-link-textarea").val();
  load_object_by_guid(sLink, false);
}
function load_object_by_guid(sLink, bReplaceLastState) {
  if (sLink !== null && sLink !== "") {
    try {
      if (sLink.substr(0, 7) === "matrix_") {
        var sHyper = sLink.substr(7);
        if (sHyper !== null && sHyper !== "") {
          load_object(
            "matrix",
            "",
            "",
            sHyper,
            "Matrix - " + sHyper,
            "images/element16/matrix.png"
          );
        }
      } else {
        sLink = sLink.replace(/^\s+|\s+$/gm, "");
        sLink = sLink.replace(/[{}]/g, "");
        sLink = sLink.replace("%7B", "");
        sLink = sLink.replace("%7D", "");
        if (sLink !== null && sLink !== "") {
          if (
            sLink.match(
              /^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}$/
            )
          ) {
            $.ajax({
              type: "GET",
              cache: false,
              url: "data_api/lookup_object.php",
              data: { rawguid: sLink },
              beforeSend: function () {
                $("#navbar-busy-loader").show();
              },
              success: function (sResponse) {
                sResponse = str_trim(sResponse);
                if (sResponse.substr(0, 8) == "success:") {
                  var sGUID = str_trim(sResponse.substr(8));
                  load_diagram_object(sGUID, bReplaceLastState);
                } else {
                  var sErrorMsg = sResponse.substr(7);
                  if (check_fatal_error(sErrorMsg)) {
                    webea_alert(
                      get_translate_string("guid_not_found_in_model")
                    );
                  }
                }
              },
              error: function (jqXHR) {
                webea_alert(
                  get_translate_string("error_occurred_while_locating_guid") +
                    "\n\t" +
                    jqXHR.responseText
                );
              },
              complete: function () {
                $("#navbar-busy-loader").hide();
              },
            });
          } else {
            webea_alert(get_translate_string("invalid_guid_format"));
          }
        } else {
          webea_alert(get_translate_string("blank_guid_supplied"));
        }
      }
    } catch (err) {
      webea_alert(get_translate_string("invalid_webea_guid"));
    }
  }
  $("#main-page-overlay").hide();
  $("#webea-goto-link-dialog").hide();
}
function load_object2_fromstring(s, bAdd2History) {
  if (typeof s !== "undefined") {
    if (s !== "") {
      var a = JSON.parse(s);
      if (a !== null) {
        var sGUID = "";
        var sHasChild = "";
        var sLinkType = "";
        var sHyper = "";
        var sName = "";
        var sImageURL = "";
        sGUID = a["guid"];
        sHasChild = a["haschild"];
        sLinkType = a["linktype"];
        sHyper = a["hyper"];
        sName = a["name"];
        sImageURL = a["imageurl"];
        load_object2(
          sGUID,
          sHasChild,
          sLinkType,
          sHyper,
          sName,
          sImageURL,
          bAdd2History
        );
      }
    }
  }
}
function webea_alert(sText, sTitle) {
  var sConsoleStr = "";
  if (sText !== null && sText !== "") {
    if (typeof sTitle !== "undefined" && sTitle !== "") {
      $("#webea-messagebox-title").text(sTitle);
      sConsoleStr = sTitle + " ";
    }
    $("#webea-messagebox-line-value-message").html(sText);
    $("#main-page-overlay").show();
    $("#webea-messagebox-dialog").show();
    Write2Console(sConsoleStr + sText);
  }
}
function webea_success_message(sText) {
  if (sText.length > 2000 && sText.includes("<tr>") == true) {
    Write2Console(sText);
    sText = "Error:Invalid message format,see browser console for details";
  } else {
    sText = str_trim(sText);
  }
  if (sText !== null && sText !== "") {
    $("#webea-success-message").stop();
    $("#webea-success-message").hide();
    $("#webea-error-message").stop();
    $("#webea-error-message").hide();
    $("#webea-success-message-text").html(sText);
    $("#webea-success-message").fadeIn(300);
  }
  $("#webea-success-message").delay(3000).fadeOut(300);
}
function webea_error_message(sText) {
  if (sText.length > 2000 && sText.includes("<tr>") == true) {
    Write2Console(sText);
    sText = "Error:Invalid message format,see browser console for details";
  } else {
    sText = str_trim(sText);
  }
  if (sText !== null && sText !== "") {
    $("#webea-success-message").stop();
    $("#webea-success-message").hide();
    $("#webea-error-message").stop();
    $("#webea-error-message").hide();
    $("#webea-error-message-text").html(sText);
    $("#webea-error-message").fadeIn(300);
  }
  $("#webea-error-message").delay(5000).fadeOut(300);
}
function webea_warning_message(sText) {
  if (sText.length > 2000 && sText.includes("<tr>") == true) {
    Write2Console(sText);
    sText = "Error:Invalid message format,see browser console for details";
  } else {
    sText = str_trim(sText);
  }
  if (sText !== null && sText !== "") {
    $("#webea-warning-message").stop();
    $("#webea-warning-message").hide();
    $("#webea-warning-message-text").html(sText);
    $("#webea-warning-message").fadeIn(300);
  }
  $("#webea-warning-message").delay(5000).fadeOut(300);
}
function OnShowStereotypeList(e, sStereotypeCSV) {
  if (sStereotypeCSV !== "") {
    var sHTML = "";
    var a = sStereotypeCSV.split(",");
    if (a.length > 0) {
      for (var i = 0; i < a.length; i++) {
        sHTML += '<div class="webea-stereotype-list-item">' + a[i] + "</div>";
      }
      document.getElementById("webea-stereotype-list-items").innerHTML = sHTML;
      var eleStereoLst = document.getElementById("webea-stereotype-list");
      if (eleStereoLst != null) {
        eleStereoLst.style.right = "";
        eleStereoLst.style.top = e.clientY + "px";
        eleStereoLst.style.left = e.clientX - 30 + "px";
        eleStereoLst.style.display = "block";
        var iRightEdge =
          parseInt(eleStereoLst.style.left) +
          parseInt(eleStereoLst.offsetWidth);
        var iCliWidth = window.innerWidth;
        if (iRightEdge > iCliWidth) {
          eleStereoLst.style.left = "";
          eleStereoLst.style.right = "10px";
        }
      }
    }
  }
}
function show_menu(element) {
  if (element != null) {
    $(element.nextElementSibling).toggle();
  }
}
function set_discuss_state(sGUID, type, value, tooltip, ele) {
  var sPayload = "guid=" + sGUID + "&type=" + type + "&value=" + value;
  var requestSettings = {
    type: "POST",
    url: "./data_api/update_discussionstate.php",
    data: sPayload,
    success: function (response) {
      var sResults = str_trim(response);
      if (sResults === "") {
        if (type === "status") {
          if (
            value === "Open" ||
            value === "Awaiting Review" ||
            value === "Closed"
          ) {
            el = $(ele).parent().parent().prev().get(0);
            if (el != null) {
              var sLC = String(value);
              sLC = sLC.toLowerCase();
              if (sLC == "awaiting review") sLC = "await";
              else if (sLC == "closed") sLC = "complete";
              el.setAttribute("class", "propsprite-discussstatus" + sLC);
              el.setAttribute("title", tooltip);
            }
          }
          $(ele).parent().parent().hide();
          document
            .getElementById("statusmenu-content-" + sGUID)
            .setAttribute("style", "display:none");
        } else if (type === "priority") {
          if (
            value === "High" ||
            value === "Medium" ||
            value === "Low" ||
            value === "None"
          ) {
            el = document.getElementById("priorityimage_" + sGUID);
            if (el != null) {
              var sLC = String(value);
              sLC = sLC.toLowerCase();
              if (sLC == "medium") sLC = "med";
              el.setAttribute("class", "propsprite-discusspriority" + sLC);
              el.setAttribute("title", tooltip);
            }
          }
          document
            .getElementById("prioritymenu-content-" + sGUID)
            .setAttribute("style", "display:none");
        }
        load_server_systemoutput();
      } else {
        if (check_fatal_error(sResults)) {
          webea_alert(
            get_translate_string("error_setting_discussion_state") +
              " " +
              sResults
          );
        }
      }
    },
    error: function (jqXHR) {
      webea_alert("set_discuss_state:" + jqXHR.responseText);
    },
  };
  $.ajax(requestSettings);
}
$(document).mouseup(function (e) {
  var container = $(".statusmenu-content");
  if (!container.is(e.target) && container.has(e.target).length === 0) {
    container.hide();
  }
  var container = $(".prioritymenu-content");
  if (!container.is(e.target) && container.has(e.target).length === 0) {
    container.hide();
  }
  $("#webea-stereotype-list").hide();
});
function hide_menu(e, buttonID, menuID) {
  var button = $(buttonID);
  var container = $(menuID);
  if (
    !container.is(e.target) &&
    container.has(e.target).length === 0 &&
    !button.is(e.target)
  ) {
    container.hide();
  }
}
function check_fatal_error(sErrorMsg) {
  var bOK = true;
  if (sErrorMsg == "") {
    var sErrorCode = $("#webea-last-oslc-error-code").text();
    var sErrorMsg = $("#webea-last-oslc-error-msg").text();
    if (sErrorCode !== "") {
      sErrorMsg = sErrorMsg.toLowerCase();
      if (
        sErrorCode === "403" ||
        sErrorMsg === "invalid user security identifier"
      ) {
        show_fatal_timeout(true);
        bOK = false;
      }
    }
  } else {
    if (
      sErrorMsg == "403 - Invalid User Security Identifier" ||
      sErrorMsg == "403 - Invalid User Authentication Identifier"
    ) {
      bOK = false;
      show_fatal_timeout(true);
    }
  }
  return bOK;
}
function show_fatal_timeout(bIsOSLC) {
  $("#main-page-overlay").show();
  $("#webea-session-timeout").show();
  if (bIsOSLC) {
    $("#session-timeout-type2").show();
  } else {
    $("#session-timeout-type2").hide();
  }
  window.clearInterval(g_TimerRef);
}
function set_iframe_content(sIFrameID, sHTML, sFilename) {
  eleIFrame = document.getElementById(sIFrameID);
  if (eleIFrame) {
    if (sFilename === "") {
      if (eleIFrame.srcdoc === undefined) {
        $("#" + sIFrameID)
          .contents()
          .find("html")
          .html(sHTML);
      } else {
        var container = eleIFrame.parentNode;
        container.removeChild(eleIFrame);
        eleIFrame.setAttribute(
          "src",
          "data:text/html;charset=utf-8," + escape(sHTML)
        );
        container.appendChild(eleIFrame);
      }
    } else {
      var container = eleIFrame.parentNode;
      container.removeChild(eleIFrame);
      eleIFrame.setAttribute("src", sFilename);
      container.appendChild(eleIFrame);
    }
  }
}
function show_browser() {
  bIsDiagram = $("#main-diagram-image").length;
  bBrowserIsLoaded = $("#main-browser-view").length;
  bBrowserIsHidden = $("#main-browser-view").is(":hidden");
  bBrowserIsEnabled = $("#mainsprite-navbarbrowsericon").hasClass(
    "mainsprite-navbarbrowsercollapse"
  );
  if (bBrowserIsEnabled) {
    $("#hamburger-pnl-browser").removeClass("mainsprite-tick");
    bShow = "false";
  } else {
    $("#hamburger-pnl-browser").addClass("mainsprite-tick");
    bShow = "true";
  }
  $.get(
    "./data_api/set_setting.php?varname=show_browser&varval=" + bShow,
    function (data) {
      if (bIsDiagram && bBrowserIsLoaded) {
        $("#main-browser-view").toggle();
        if (bBrowserIsEnabled) {
          $("#mainsprite-navbarbrowsericon")
            .removeClass("mainsprite-navbarbrowsercollapse")
            .addClass("mainsprite-navbarbrowserexpand");
          $("#navbar-browser-button").prop(
            "title",
            get_translate_string("navbar_show_browser")
          );
          if ($("#main-diagram-image").hasClass("show-browserminiprops")) {
            $("#main-diagram-image")
              .removeClass("show-browserminiprops")
              .addClass("show-miniprops");
          }
          if ($("#main-diagram-image").hasClass("show-browser")) {
            $("#main-diagram-image").removeClass("show-browser");
          }
        } else {
          $("#mainsprite-navbarbrowsericon")
            .removeClass("mainsprite-navbarbrowserexpand")
            .addClass("mainsprite-navbarbrowsercollapse");
          $("#navbar-browser-button").prop(
            "title",
            get_translate_string("navbar_hide_browser")
          );
          if ($("#main-diagram-image").hasClass("show-miniprops")) {
            $("#main-diagram-image")
              .removeClass("show-miniprops")
              .addClass("show-browserminiprops");
          } else {
            $("#main-diagram-image").addClass("show-browser");
          }
        }
      } else {
        refresh_current();
      }
    }
  );
}
function show_mini_properties(bShow) {
  bIsDiagram = $("#main-diagram-image").length;
  bPropsIsLoaded = $("#main-mini-properties-view").length;
  bPropsIsHidden = $("#main-mini-properties-view").is(":hidden");
  if (
    $("#mainsprite-navbarpropsicon").hasClass("mainsprite-navbarpropscollapse")
  ) {
    $("#hamburger-pnl-miniprops").removeClass("mainsprite-tick");
    bShow = "false";
  } else {
    $("#hamburger-pnl-miniprops").addClass("mainsprite-tick");
    bShow = "true";
  }
  if (bIsDiagram) {
    if (bPropsIsLoaded) {
      $.get(
        "./data_api/set_setting.php?varname=show_miniproperties&varval=" +
          bShow,
        function (data) {
          if (
            $("#mainsprite-navbarpropsicon").hasClass(
              "mainsprite-navbarpropscollapse"
            )
          ) {
            $("#mainsprite-navbarpropsicon")
              .addClass("mainsprite-navbarpropsexpand")
              .removeClass("mainsprite-navbarpropscollapse");
            $("#navbar-properties-button").prop(
              "title",
              get_translate_string("navbar_show_properties")
            );
            if ($("#main-diagram-image").hasClass("show-miniprops")) {
              $("#main-diagram-image").removeClass("show-miniprops");
            }
            if ($("#main-diagram-image").hasClass("show-browserminiprops")) {
              $("#main-diagram-image")
                .removeClass("show-browserminiprops")
                .addClass("show-browser");
            }
          } else {
            $("#mainsprite-navbarpropsicon")
              .addClass("mainsprite-navbarpropscollapse")
              .removeClass("mainsprite-navbarpropsexpand");
            $("#navbar-properties-button").prop(
              "title",
              get_translate_string("navbar_hide_properties")
            );
            if ($("#main-diagram-image").hasClass("show-browser")) {
              $("#main-diagram-image")
                .removeClass("show-browser")
                .addClass("show-browserminiprops");
            } else {
              $("#main-diagram-image").addClass("show-miniprops");
            }
          }
          $("#main-mini-properties-view").toggle();
        }
      );
    } else {
      $.get(
        "./data_api/set_setting.php?varname=show_miniproperties&varval=" +
          bShow,
        function (data) {
          refresh_current();
        }
      );
    }
  } else {
    $.get(
      "./data_api/set_setting.php?varname=show_miniproperties&varval=" + bShow,
      function (data) {
        refresh_current();
      }
    );
  }
}
function show_system_output(bShow) {
  if ($("#hamburger-systemoutput").hasClass("mainsprite-tick")) {
    $("#hamburger-systemoutput").removeClass("mainsprite-tick");
    bShow = "false";
  } else {
    $("#hamburger-systemoutput").addClass("mainsprite-tick");
    bShow = "true";
  }
  $.get(
    "./data_api/set_setting.php?varname=show_system_output&varval=" + bShow,
    function (data) {
      window.location.reload();
    }
  );
}
function OnFormViewMatrix(event) {
  event.preventDefault();
  var sPayload = $("#matrix-profile").val();
  sSourcePackage = $("#matrix-source-package").html();
  sTargetPackage = $("#matrix-target-package").html();
  sSourceType = $("#matrix-source-type").html();
  sTargetType = $("#matrix-target-type").html();
  sLinkType = $("#matrix-link-type").html();
  sLinkDirection = $("#matrix-link-direction").html();
  if ($("#matrix-profile").prop("selectedIndex") === 0) {
    webea_error_message("Please select a Profile");
  } else {
    if (
      sSourcePackage == "" ||
      sTargetPackage == "" ||
      sSourceType == "" ||
      sTargetType == "" ||
      sLinkType == "" ||
      sLinkDirection == ""
    ) {
      webea_error_message(get_translate_string("matrix_profile_incomplete"));
    } else {
      load_object(
        "matrix",
        "",
        "",
        sPayload,
        "Matrix - " + sPayload,
        "images/element16/matrix.png"
      );
    }
  }
}
function OnSelectMatrixProfile(e) {
  $("#main-busy-loader1").show();
  sPayload = "profile=" + encodeURIComponent(e.options[e.selectedIndex].text);
  if (e.selectedIndex === 0) {
    $("#matrix-source-package").html("");
    $("#matrix-target-package").html("");
    $("#matrix-source-type").html("");
    $("#matrix-target-type").html("");
    $("#matrix-link-type").html("");
    $("#matrix-link-direction").html("");
    $("#main-busy-loader1").hide();
    $("#main-busy-loader1").hide();
    $("#matrix-profile-view-matrix").prop("disabled", true);
  } else {
    $("#matrix-profile-view-matrix").prop("disabled", false);
    $.ajax({
      type: "POST",
      cache: false,
      dataType: "text json",
      url: "./data_api/get_matrixprofilesettings.php",
      data: sPayload,
      success: function (aSettings) {
        if (aSettings != null) {
          var sLastOSLCError = aSettings["lastoslcerror"];
          if (sLastOSLCError === "") {
            var sSourcePackageName = aSettings["sourcepackagename"];
            var sTargetPackageName = aSettings["targetpackagename"];
            var sSourcePackageGUID = aSettings["sourcepackageguid"];
            var sTargetPackageGUID = aSettings["targetpackageguid"];
            var sSourceType = aSettings["sourcetype"];
            var sTargetType = aSettings["targettype"];
            var sLinkType = aSettings["linktype"];
            var sLinkDirection = aSettings["linkdirection"];
            var aOptions = aSettings["options"];
            $("#matrix-source-package").html(sSourcePackageName);
            $("#matrix-target-package").html(sTargetPackageName);
            $("#matrix-source-type").html(sSourceType);
            $("#matrix-target-type").html(sTargetType);
            $("#matrix-link-type").html(sLinkType);
            $("#matrix-link-direction").html(sLinkDirection);
          } else {
            if (check_fatal_error(sLastOSLCError)) {
              $("#matrix-source-package").html("");
              $("#matrix-target-package").html("");
              $("#matrix-source-type").html("");
              $("#matrix-target-type").html("");
              $("#matrix-link-type").html("");
              $("#matrix-link-direction").html("");
            }
          }
        }
      },
      error: function (jqXHR) {
        if (check_fatal_error(sLastOSLCError)) {
          webea_alert("Error:" + jqXHR.responseText);
        }
      },
      complete: function (jqXHR) {
        $("#main-busy-loader1").hide();
      },
    });
  }
}
function OnSetAllCheckboxes(sCheckState) {
  var b = sCheckState === "true" || sCheckState === "True";
  $("input").prop("checked", b);
}
function get_translate_string(sElementID) {
  var sRet = "";
  var sText = $("#" + sElementID).text();
  if (sText !== "") {
    sRet = sText;
  } else {
    Write2Console(
      "get_translate_string:NO value found for ElementID=" + sElementID
    );
    sRet = sElementID.replace("_", " ");
  }
  return sRet;
}
function load_server_systemoutput() {
  var sysout = $("#webea-system-output");
  if (sysout.length) {
    var sStatement = "";
    var iData;
    var sSystemOutputHTML = "";
    var data = document.getElementsByClassName("webea-sysout-data");
    for (iData = 0; iData < data.length; iData++) {
      if (data[iData].hasChildNodes()) {
        var children = data[iData].childNodes;
        for (var i = 0; i < children.length; i++) {
          sStatement = children[i].innerHTML;
          sStatement = sStatement.replace(
            '<div class="webea-sysout-data-entry">',
            ""
          );
          sStatement = sStatement.replace("</div>", "");
          sSystemOutputHTML += '<div class="sysout-entry">';
          sSystemOutputHTML += sStatement;
          sSystemOutputHTML += "</div>";
        }
      }
      data[iData].innerHTML = "";
      sStatement = "";
    }
    sysout.append(sSystemOutputHTML);
    sysout[0].parentNode.scrollTop = sysout[0].scrollHeight;
  }
}
function OnClickClearSystemOutput() {
  var sysout = $("#webea-system-output");
  if (sysout.length) {
    sysout.html("");
  }
}
function OnClickCopySystemOutput() {
  var sysout = $("#webea-system-output");
  if (sysout.length) {
    var sMessages = sysout.html();
    sMessages = sMessages.replace(/<div class="sysout-entry">/g, "");
    sMessages = sMessages.replace(/<\/div>/g, "\r\n");
    copyTextToClipboard(sMessages);
  }
}
function copyTextToClipboard(text) {
  var textArea = document.createElement("textarea");
  textArea.style.position = "fixed";
  textArea.style.top = 0;
  textArea.style.left = 0;
  textArea.style.width = "2em";
  textArea.style.height = "2em";
  textArea.style.padding = 0;
  textArea.style.border = "none";
  textArea.style.outline = "none";
  textArea.style.boxShadow = "none";
  textArea.style.background = "transparent";
  textArea.value = text;
  document.body.appendChild(textArea);
  textArea.select();
  try {
    var successful = document.execCommand("copy");
    if (successful) {
      webea_success_message("System Output was copied to clipboard");
    } else {
      webea_error_message("Unable to copy the System Output to clipboard");
    }
  } catch (err) {
    webea_error_message("Unable to copy the System Output to clipboard");
    console.log("Oops,unable to copy");
  }
  document.body.removeChild(textArea);
}
function toggle_visibility(element) {
  $(element).toggle();
}
function check_browser_visibility() {
  if (
    $("#hamburger-pnl-browser").hasClass("mainsprite-tick") &&
    $("#main-browser-view").css("display") === "none"
  ) {
    sError = get_translate_string("browser_is_disabled");
    $("#hamburger-pnl-browser").removeClass("mainsprite-tick");
    $.get(
      "./data_api/set_setting.php?varname=show_browser&varval=" + "false",
      function (data) {}
    );
    if ($("#hamburger-pnl-miniprops").hasClass("mainsprite-tick")) {
      $("#hamburger-pnl-miniprops").removeClass("mainsprite-tick");
      $.get(
        "./data_api/set_setting.php?varname=show_miniproperties&varval=" +
          "false",
        function (data) {}
      );
      sError = get_translate_string("browser_and_miniprops_are_disabled");
    }
  }
}
function show_section(
  id,
  sGUID,
  sHasChild,
  sLinkType,
  sHyper,
  sName,
  sImageURL,
  thisButton
) {
  id = id.replace(/\s/g, "");
  buttonID = "#props-tab-" + id;
  if (id !== "location") {
    id = id.substring(0, id.length - 1);
  }
  id = "#" + id + "-section";
  if (id === "#summar-section") id = "#review-section";
  $(".properties-tab").css("background-color", "");
  $(buttonID).css("background-color", "#eee");
  $(id).show();
  $(id).siblings().hide();
}
function load_prop_details(
  id,
  sGUID,
  sHasChild,
  sLinkType,
  sHyper,
  sName,
  sImageURL
) {
  var aPropType = sLinkType.split("-");
  var propType = aPropType[1];
  propDetailsID = "#" + propType + "-details";
  propSectionID = "#" + propType + "-section";
  id = "#" + id;
  console.log(id);
  console.log("propType = " + propType);
  $(propDetailsID).show();
  $(id).show();
  $(id).siblings().hide();
  $(propSectionID).hide();
}
function select_feature(id, label, guid) {
  $.get(
    "./data_api/set_setting.php?varname=selected_feature&varval=" + id,
    function (data) {}
  );
  load_miniprops_object(guid);
}
function toggle_scrolling() {
  sScrollState = $("#scroll-mode").html();
  if (sScrollState === "Off") {
    $.get(
      "./data_api/set_setting.php?varname=ios_scroll&varval=true",
      function (data) {}
    );
    $("#scroll-mode").html("On");
    $("#scroll-mode-icon")
      .addClass("propsprite-greendot")
      .removeClass("propsprite-reddot");
    if ($("#diagrammap").length) $("#diagrammap").next().get(0).useMap = "";
  } else if (sScrollState === "On") {
    $.get(
      "./data_api/set_setting.php?varname=ios_scroll&varval=false",
      function (data) {}
    );
    $("#scroll-mode-icon")
      .addClass("propsprite-reddot")
      .removeClass("propsprite-greendot");
    $("#scroll-mode").html("Off");
    if ($("#diagrammap").length)
      $("#diagrammap").next().get(0).useMap = "#diagrammap";
  }
}
