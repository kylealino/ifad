var __mysys_apps = new __mysysapps();
function __mysysapps() {  
  this.initDivEl = function(cdiv) {
    jQuery(cdiv).html(''); 
  };
  
  
  this.oa_trim_space = function(xstr) { //REMOVING THE TRAILING SPACE TO THE TXTBOX 
    xstr = xstr.replace(/^\s\s*/, '').replace(/\s\s*$/, ''); //REGULAR EXPRESSION
    return xstr;
  };
  
  this.oa_ommit_pound_and = function(xstr) {
    var xregexp = new RegExp('[#&]','g');
    xstr = xstr.replace(xregexp,'');
    xstr = xstr.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    return xstr;
  };

  this.oa_ommit_comma = function(xstr) {
    var xregexp = new RegExp('[,]','g');
    xstr = xstr.replace(xregexp,'');
    xstr = xstr.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    return xstr;
  };
  
  this.oa_addCommas = function(nStr)  { 
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
  };  //oa_addCommas
  
  this.isNumber = function(nval) { 
    return !isNaN(parseFloat(nval)) && isFinite(nval);
  }; 
  
  this.isDate = function(value) { 
    try {
      //Change the below values to determine which format of date you wish to check. It is set to dd/mm/yyyy by default.
      var DayIndex = 1;
      var MonthIndex = 0;
      var YearIndex = 2;
   
      value = value.replace(/-/g, "/").replace(/\./g, "/"); 
      var SplitValue = value.split("/");
      var OK = true;
      if (!(SplitValue[DayIndex].length == 1 || SplitValue[DayIndex].length == 2)) {
        OK = false; 
      }
      if (OK && !(SplitValue[MonthIndex].length == 1 || SplitValue[MonthIndex].length == 2)) {
        OK = false;
      }
      if (OK && SplitValue[YearIndex].length != 4) {
        OK = false;
      }
      if (OK) {
        var Day = parseInt(SplitValue[DayIndex], 10);
        var Month = parseInt(SplitValue[MonthIndex], 10);
        var Year = parseInt(SplitValue[YearIndex], 10);
   
        if (OK = ((Year > 1800) && (Year <= new Date().getFullYear() + 100))) {  
          if (OK = (Month <= 12 && Month > 0)) {
            var LeapYear = (((Year % 4) == 0) && ((Year % 100) != 0) || ((Year % 400) == 0));
   
            if (Month == 2) {
              OK = LeapYear ? Day <= 29 : Day <= 28;
            }
            else {
              if ((Month == 4) || (Month == 6) || (Month == 9) || (Month == 11)) {
                OK = (Day > 0 && Day <= 30);
              }
              else {
                OK = (Day > 0 && Day <= 31);
              }
            }
          }
        }
      }
      return OK;
    }
    catch (e) {
      return false;
    }     
  };  //isDate

  this.isDate_yyyymmdd = function(value) { 
    try {
      //Change the below values to determine which format of date you wish to check. It is set to yyyy-mm-dd by default.
      var DayIndex = 2;
      var MonthIndex = 1;
      var YearIndex = 0;
   
      value = value.replace(/-/g, "-").replace(/\./g, "-"); 
      var SplitValue = value.split("-");
      var OK = true;
      if (!(SplitValue[DayIndex].length == 1 || SplitValue[DayIndex].length == 2)) {
        OK = false; 
      }
      if (OK && !(SplitValue[MonthIndex].length == 1 || SplitValue[MonthIndex].length == 2)) {
        OK = false;
      }
      if (OK && SplitValue[YearIndex].length != 4) {
        OK = false;
      }
      if (OK) {
        var Day = parseInt(SplitValue[DayIndex], 10);
        var Month = parseInt(SplitValue[MonthIndex], 10);
        var Year = parseInt(SplitValue[YearIndex], 10);
   
        if (OK = ((Year > 1800) && (Year <= new Date().getFullYear() + 100))) {  
          if (OK = (Month <= 12 && Month > 0)) {
            var LeapYear = (((Year % 4) == 0) && ((Year % 100) != 0) || ((Year % 400) == 0));
   
            if (Month == 2) {
              OK = LeapYear ? Day <= 29 : Day <= 28;
            }
            else {
              if ((Month == 4) || (Month == 6) || (Month == 9) || (Month == 11)) {
                OK = (Day > 0 && Day <= 30);
              }
              else {
                OK = (Day > 0 && Day <= 31);
              }
            }
          }
        }
      }
      return OK;
    }
    catch (e) {
      return false;
    }     
  };  //isDate_yyyymmdd
  

  this.isMyDate = function(value) { 
    try {
      //Change the below values to determine which format of date you wish to check. It is set to dd/mm/yyyy by default.
      var DayIndex = 1;
      var MonthIndex = 0;
      var YearIndex = 2;
   
      value = value.replace(/-/g, "/").replace(/\./g, "/"); 
      var SplitValue = value.split("/");
      var OK = true;
      if (!(SplitValue[DayIndex].length == 1 || SplitValue[DayIndex].length == 2)) {
        OK = false; 
      }
      if (OK && !(SplitValue[MonthIndex].length == 1 || SplitValue[MonthIndex].length == 2)) {
        OK = false;
      }
      if (OK && SplitValue[YearIndex].length != 4) {
        OK = false;
      }
      if (OK) {
        var Day = parseInt(SplitValue[DayIndex], 10);
        var Month = parseInt(SplitValue[MonthIndex], 10);
        var Year = parseInt(SplitValue[YearIndex], 10);
   
        if (OK = ((Year > 1900) && (Year <= 3000))) {  
          if (OK = (Month <= 12 && Month > 0)) {
            var LeapYear = (((Year % 4) == 0) && ((Year % 100) != 0) || ((Year % 400) == 0));
   
            if (Month == 2) {
              OK = LeapYear ? Day <= 29 : Day <= 28;
            }
            else {
              if ((Month == 4) || (Month == 6) || (Month == 9) || (Month == 11)) {
                OK = (Day > 0 && Day <= 30);
              }
              else {
                OK = (Day > 0 && Day <= 31);
              }
            }
          }
        }
      }
      return OK;
    }
    catch (e) {
      return false;
    }     
  };  //isMyDate

    this.mathHelper = {
        getMoney: function(value) {
            return parseFloat(accounting.formatNumber(value, 2, ''));
        },
    };
  
  this.render_datepicker = function(id) {
    jQuery(id).datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        showOtherMonths: true, 
        selectOtherMonths: true,
      });
    
  };

  this.render_datepicker2 = function(id,nmonths) {
    jQuery(id).datepicker({
        numberOfMonths: nmonths,
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        showOtherMonths: true, 
        selectOtherMonths: true,
      });
    
  };
  
    this.render_datepicker_range = function(id1,id2,myid) {
      jQuery(function() {
        var dates = $(id2 + ',' + id1).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          changeYear: true,
          showButtonPanel: true,
          showOtherMonths: true, 
          selectOtherMonths: true,
          numberOfMonths: 1,
          onSelect: function(selectedDate) {
            var option = this.id == myid ? "minDate" : "maxDate";
            var instance = $(this).data("datepicker");
            var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
            dates.not(this).datepicker("option", option, date);
          }
        });
    });
  };

    this.mepreloader = function(id,lshow) { 
      jQuery(function() { 
        var meploader = jQuery('#' + id);
        if(lshow) {
          meploader.show();
        } else {
          meploader.hide();
        }
      });
    };  //end mepreloader

	this.meTBL_SetCellPadding = function(elemID,cellpadding,borderstyle) {
        var metable = document.getElementById(elemID);
        //metable.cellPadding = 3;
        metable.cellPadding = cellpadding;
        //metable.style.border = "1px solid #F6F5F4";
        if(borderstyle !== '') {
			metable.style.border = borderstyle;
		}
    }  //end meSetCellPadding    
    
    this.__do_makeid = function(nLen)
    {
        var text = '';
        var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        for( var i=0; i < nLen; i++ ) 
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
    };  //end __do_makeid

	this.mybs_simple_toast = function(metoastcontid,metoastid,classtoast,cmesg) {
		var chtml = "<div id=\"" + metoastid + "\" class=\"toast " + classtoast + "\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\">";
		chtml += "<div class=\"d-flex\">";
		chtml += "<div class=\"toast-body\">"
		chtml += cmesg;
		chtml += "</div>";
		chtml += "<button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>";
		chtml += "</div>";
		chtml += "</div>";
		document.getElementById(metoastcontid).innerHTML = chtml;
		var myAlertToast = document.getElementById(metoastid);
		var bsAlertToast = new window.bootstrap.Toast(myAlertToast); //inizialize it
		bsAlertToast.show(); //show it 
	}; //end mybs_toast
	
	this.mybs_theme_toast_default = function(metoastcontid,metoastid,classtoast,cmesg) {
		var chtml = "<div id=\"" + metoastid + "\" class=\"toast " + classtoast + " p-0\" role=\"alert\" aria-live=\"assertive\" data-bs-autohide=\"false\">";
		chtml += "<div class=\"d-flex\">";
		chtml += "<div class=\"toast-body\">"
		chtml += cmesg;
		chtml += "</div>";
		chtml += "<button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>";
		chtml += "</div>";
		chtml += "</div>";
		document.getElementById(metoastcontid).innerHTML = chtml;
		var myAlertToast = document.getElementById(metoastid);
		var bsAlertToast = new window.bootstrap.Toast(myAlertToast); //inizialize it
		bsAlertToast.show(); //show it 
	}; //end mybs_theme_toast_default	
	
	this.mybs_theme_toast = function(metoastcontid,metoastid,classtoast,ctitle,cmesg,isAlertRole) { 
		var crolealert = (isAlertRole ? "role=\"alert\" data-bs-autohide=\"false\"" : "");
		var chtml = "<div id=\"" + metoastid + "\" " + crolealert + " class=\"toast " + classtoast + "\" aria-live=\"assertive\" >";
		chtml += "<button type=\"button\" class=\"toast-close-button\" data-bs-dismiss=\"toast\">×</button>";
		chtml += "<div class=\"toast-title\">" + ctitle + "</div>";
		chtml += "<div class=\"toast-message\">" + cmesg + "</div>";
		chtml += "</div>";
		document.getElementById(metoastcontid).innerHTML = chtml;
		var myAlertToast = document.getElementById(metoastid);
		var bsAlertToast = new window.bootstrap.Toast(myAlertToast); //inizialize it
		bsAlertToast.show(); //show it 
	}; //end mybs_theme_toast_default
	
}; //end main __mysysapps

var meSys_getFromBetween = {
	results:[],
	string:"",
	getFromBetween:function (sub1,sub2) {
		if(this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0) return false;
		var SP = this.string.indexOf(sub1)+sub1.length;
		var string1 = this.string.substr(0,SP);
		var string2 = this.string.substr(SP);
		var TP = string1.length + string2.indexOf(sub2);
		return this.string.substring(SP,TP);
	},
	removeFromBetween:function (sub1,sub2) {
		if(this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0) return false;
		var removal = sub1+this.getFromBetween(sub1,sub2)+sub2;
		this.string = this.string.replace(removal,"");
	},
	getAllResults:function (sub1,sub2) {
		// first check to see if we do have both substrings
		if(this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0) return;

		// find one result
		var result = this.getFromBetween(sub1,sub2);
		// push it to the results array
		this.results.push(result);
		// remove the most recently found one from the string
		this.removeFromBetween(sub1,sub2);

		// if there's more substrings
		if(this.string.indexOf(sub1) > -1 && this.string.indexOf(sub2) > -1) {
			this.getAllResults(sub1,sub2);
		}
		else return;
	},
	get:function (string,sub1,sub2) {
		this.results = [];
		this.string = string;
		this.getAllResults(sub1,sub2);
		return this.results;
	}
}; //end meSys_getFromBetween
