<?php
namespace App\Models;
use CodeIgniter\Model;

class MyLibzSysModel extends Model
{
	
	public function random_string($length) {
		$key = '';
		$keys = array_merge(range(0, 9), range('a', 'z'));
		for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}
		return $key;
	}  //end random_string
    public function oa_nospchar($cdatame='') { 
        $cddata = '';
        if(!empty($cdatame)) { 
            $cddata = str_replace(',','',$cdatame);
            $cddata = str_replace('-','',$cddata);
            $cddata = str_replace('[','',$cddata);
            $cddata = str_replace(']','',$cddata);
            $cddata = str_replace('{','',$cddata);
            $cddata = str_replace('}','',$cddata);
            $cddata = str_replace('(','',$cddata);
            $cddata = str_replace(')','',$cddata);
            $cddata = str_replace('|','',$cddata);
            $cddata = str_replace(';','',$cddata);
            $cddata = str_replace(':','',$cddata);
            $cddata = str_replace('%','',$cddata);
            $cddata = str_replace('@','',$cddata);
            $cddata = str_replace("'",'',$cddata);
            $cddata = str_replace('"','',$cddata);
            $cddata = str_replace('^','',$cddata);
            $cddata = str_replace('&','',$cddata);
            $cddata = str_replace('#','',$cddata);
        }
        return $cddata;
    }
    
	public function oa_nospchar2($cdatame='') { 
		$cddata = '';
        if(!empty($cdatame)) { 		
			$cddata = str_replace(',','',$cdatame);
			$cddata = str_replace('-','',$cddata);
			$cddata = str_replace('[','',$cddata);
			$cddata = str_replace(']','',$cddata);
			$cddata = str_replace('{','',$cddata);
			$cddata = str_replace('}','',$cddata);
			$cddata = str_replace('(','',$cddata);
			$cddata = str_replace(')','',$cddata);
			$cddata = str_replace('|','',$cddata);
			$cddata = str_replace(';','',$cddata);
			$cddata = str_replace(':','',$cddata);
			$cddata = str_replace('%','',$cddata);
			$cddata = str_replace('@','',$cddata);
			$cddata = str_replace("'",'',$cddata);
			$cddata = str_replace('"','',$cddata);
			$cddata = str_replace('^','',$cddata);
			$cddata = str_replace('&','',$cddata);
			$cddata = str_replace(' ','',$cddata);
		}
		return $cddata;
	} //end oa_nospchar2
	
	public function oa_nospchar3($cdatame='') { 
		$cddata = '';
        if(!empty($cdatame)) { 		
			$cddata = str_replace(',','',$cdatame);
			$cddata = str_replace('[','',$cddata);
			$cddata = str_replace(']','',$cddata);
			$cddata = str_replace('{','',$cddata);
			$cddata = str_replace('}','',$cddata);
			$cddata = str_replace('(','',$cddata);
			$cddata = str_replace(')','',$cddata);
			$cddata = str_replace('|','',$cddata);
			$cddata = str_replace(';','',$cddata);
			$cddata = str_replace(':','',$cddata);
			$cddata = str_replace('%','',$cddata);
			$cddata = str_replace('@','',$cddata);
			$cddata = str_replace("'",'',$cddata);
			$cddata = str_replace('"','',$cddata);
			$cddata = str_replace('^','',$cddata);
			$cddata = str_replace('&','',$cddata);
			$cddata = str_replace(' ','',$cddata);
		}
		return $cddata;
	} //end oa_nospchar3
	
	public function oa_nospchar4($cdatame='') { 
		$cddata = '';
        if(!empty($cdatame)) { 		
			$cddata = str_replace(',','',$cdatame);
			$cddata = str_replace('[','',$cddata);
			$cddata = str_replace(']','',$cddata);
			$cddata = str_replace('{','',$cddata);
			$cddata = str_replace('}','',$cddata);
			$cddata = str_replace('|','',$cddata);
			$cddata = str_replace(';','',$cddata);
			$cddata = str_replace(':','',$cddata);
			$cddata = str_replace('%','',$cddata);
			$cddata = str_replace('@','',$cddata);
			$cddata = str_replace("'",'',$cddata);
			$cddata = str_replace('"','',$cddata);
			$cddata = str_replace('^','',$cddata);
			$cddata = str_replace('&','',$cddata);
		}
		return $cddata;
	} //end oa_nospchar4	
	
    public function oa_no_commas($xval) { 
        $xval = str_replace(',','',trim($xval));
        return $xval;
    }
    
    public function meval($xval,$xtype='') { 
		if($xtype == 'num') { 
			$xval = (empty($xval) ? 0 : ($xval + 0));
		} else { 
			$xval = (empty($xval) ? '' : trim($xval));
		}
		return $xval;
	}  //end meval
    
    public function mydate_yyyymmdd($angdate='') {
        //1234567890
        //08-08-2008
        return substr($angdate,6,4). '-' . substr($angdate,0,2) . '-' .substr($angdate,3,2);
    }
    
    
    public function mydate_mmddyyyy($angdate='') {
        //1234567890
        //2008-08-01
        if(!empty($angdate)){
        return substr($angdate,5,2). '/' . substr($angdate,8,2) . '/' .substr($angdate,0,4);
        }
    }

    public function mypopulist_2($myaray,$ccdata,$objname,$onaction='',$moutput='TO_ECHO',$mdeli="xOx") {
        $mesep = (empty($mdeli) ? "xOx" : $mdeli);
        $obj = '<select style= "color: #000000 !important;max-width:100%;" name="' . $objname . '" id="' . $objname . '" ' . $onaction . '>';
        if(empty($ccdata)) {
            $obj .= ' <option style= "color: #000000 !important;" selected="selected" value=""></option>' . "\n";
        }

        $ii=0; 
        $nflag=0;
        
        while($ii < count($myaray)) {
        
            $ddata = explode($mesep,$myaray[$ii]);
            $ment = rtrim($ddata[0]);
            if(strlen($ment) > 0) {
                if($ddata[0] == $ccdata) { 
                    $mselected = 'selected';
                    $nflag = 1;                    
                }
                else {
                    $mselected = '';
                }
                $obj .= ' <option style= "color: #000000 !important;" ' . $mselected . ' value="' . $ddata[0] . '">' . $ddata[1] . '</option>' . "\n";  
            } 
            $ii++;
        } //end while
        if($nflag == 0) {
            if(!empty($ccdata)) {
                //$obj .= ' <option style= "color: #000000 !important;" selected="selected" value="' . $ccdata . '"></option>' . "\n";
            } 
        }
        
        $obj .= '</select>';
        if($moutput == 'TO_ECHO') { 
            echo $obj;
        } else { 
            return $obj;
        }
    }  //end mypopulist_2

    public function myselect_dom($myarray,$ccdata,$objname,$onaction='',$moutput='TO_ECHO') {
        $mesep = (empty($mdeli) ? "xOx" : $mdeli);
        $obj = '<select style= "color: #000000 !important;max-width:100%;" name="' . $objname . '" id="' . $objname . '" ' . $onaction . '>';
        if(empty($ccdata)) {
            $obj .= ' <option style= "color: #000000 !important;" value=""></option>' . "\n";
        }

        $ii=0; 
        $nflag=0;
        
        foreach($myarray as $mearr):
        
            $ment = rtrim($mearr[1]);
            if(strlen($ment) > 0) {
                if($mearr[0] == $ccdata) { 
                    $mselected = 'selected';
                    $nflag = 1;                    
                }
                else {
                    $mselected = '';
                }
                $obj .= ' <option style= "color: #000000 !important;" ' . $mselected . ' value="' . $mearr[0] . '">' . $mearr[1] . '</option>' . "\n";  
            } 
            $ii++;
        endforeach; //end foreach
        if($nflag == 0) {
            if(!empty($ccdata)) {
                //$obj .= ' <option style= "color: #000000 !important;" selected="selected" value="' . $ccdata . '"></option>' . "\n";
            } 
        }
        
        $obj .= '</select>';
        if($moutput == 'TO_ECHO') { 
            echo $obj;
        } else { 
            return $obj;
        }
    }  //end myselect_dom    
    
    public function mypagination($npage_curr,$npage_count,$cjavafunc='__myredirected_search',$moutput='') {  
        $chtml = "
    <ul class=\"pagination pagination-sm flex-wrap\">       
        ";
        /******  build the pagination links ******/
        // if not on page 1, don't show back links
        if ($npage_curr > 1) { 
            // show << link to go back to page 1
            $chtml .= " 
            <li class=\"page-item pull-left previous\">
            <a class=\"page-link\" href=\"javascript:{$cjavafunc}('1');\" aria-label=\"Previous\">
            <span aria-hidden=\"true\">&laquo;</span>
            </a> 
            </li>
            ";
            // get previous page num
            $prevpage = $npage_curr - 1;
            // show < link to go back to 1 page
            $chtml .= "
            <li class=\"page-item pull-left\">
             <a class=\"page-link\" href=\"javascript:{$cjavafunc}('" . $prevpage . "');\"><span aria-hidden=\"true\">&lsaquo;</span></a> 
            </li>
            ";
        } // end if

        # range of num links to show
        $range = 3;

        # loop to show links to range of pages around current page
        for ($x = ($npage_curr - $range); $x < (($npage_curr + $range)  + 1); $x++) {
            // if it's a valid page number...
            if (($x > 0) && ($x <= $npage_count)) {
            // if we're on current page...
                if ($x == $npage_curr) {
                // 'highlight' it but don't make a link
                    $chtml .= " 
                    <li class=\"page-item pull-left\">
                     <a class=\"page-link\" href=\"javascript:void(0);\">
                        <b>" . number_format($x,0,'',',') . "</b>
                     </a> 
                    </li>
                    ";
            // if not current page...
            }
            else {
                // make it a link
                $chtml .= " 
                <li class=\"page-item pull-left\">
                 <a class=\"page-link\" href=\"javascript:{$cjavafunc}('" . $x . "');\">" . number_format($x,0,'',',') . "</a> 
                </li>
                ";
                } // end else
            } // end if 
        } // end for


        // if not on last page, show forward and last page links        
        if ($npage_curr != $npage_count) {
            // get next page
            $nextpage = $npage_curr + 1;
            // echo forward link for next page 
            $chtml .= " 
            <li class=\"page-item pull-left\">
             <a class=\"page-link\" href=\"javascript:{$cjavafunc}('" . $nextpage . "');\"><span aria-hidden=\"true\">&rsaquo;</span></a> 
            </li>
            ";
            // echo forward link for lastpage
            $chtml .= " 
            <li class=\"page-item pull-left\">
             <a class=\"page-link\" href=\"javascript:{$cjavafunc}('" . $npage_count . "');\" aria-label=\"Next\"><span aria-hidden=\"true\">&raquo;</span></a> 
            </li>
            ";
         } // end if
         # end build pagination links 
        $chtml .= "
            </ul>
        ";
        if($moutput == 'TO_ECHO') { 
            echo $chtml;
        } else { 
            return $chtml;
        }       
    }  //end mypagination

    public function memypreloader01($domid) {
        $chtml = "
        <div class=\"me-overlay\" id=\"{$domid}\">
            <div class=\"me-overlay-inner\">
                <div class=\"me-overlay-content\"><span class=\"me-spinner\"></span></div>
            </div>
        </div>        
        ";
        return $chtml;

    } //end memypreloader

    public function memsgbox_yesno1($domid,$ctitle='',$cmsg='') {
        $chtml = "
        <div class=\"modal\" id=\"{$domid}\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" role=\"dialog\" tabindex=\"-1\" aria-labelledby=\"staticBackdrop_{$domid}\ aria-hidden=\"true\">
            <div class=\"modal-dialog modal-dialog-centered\" role=\"document\">
                <div class=\"modal-content\">
                    <div class=\"modal-header\" id=\"{$domid}_meheader\">
                        <h4 class=\"modal-title\" id=\"static_title_{$domid}\">{$ctitle}</h4>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                    </div>
                    <div class=\"modal-body\" id=\"{$domid}_bod\">
                     {$cmsg}
                    </div>
                    <div class=\"modal-footer\">
                        <button class=\"btn btn-info btn-sm\" id=\"{$domid}_yes\" >Yes</button>
                        <button class=\"btn btn-danger btn-sm\" id=\"{$domid}_no\" data-bs-dismiss=\"modal\">No</button>
                    </div>
                </div>
            </div>
        </div>      
        ";
        return $chtml;
    } //end memsgbox_yesno1

    public function memsgbox1($domid,$ctitle='',$cmsg='') {
        $chtml = "
        <div class=\"modal\" id=\"{$domid}\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdrop_{$domid}\" aria-hidden=\"true\">
            <div class=\"modal-dialog modal-dialog-centered\">
                <div class=\"modal-content\">
                    <div class=\"modal-header bg-light p-2\" id=\"{$domid}_meheader\">
                        <h4 class=\"modal-title\" id=\"static_title_{$domid}\">{$ctitle}</h4>
                        <button type=\"button\" class=\"btn-close btn-sm\" style=\"margin: 0px -5px 0px 0px !important;\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                    </div>
                    <div class=\"modal-body p-1\" id=\"{$domid}_bod\">
                        {$cmsg}
                    </div>
                </div>
            </div>
        </div>      
        ";
        return $chtml;
    } //end memsgbox1

    public function memsgbox2($domid,$ctitle='',$cmsg='',$cmodaloptions='',$cmodalheaderoptions='',$lshowfooter=0) {
		$cfooter = "<div class=\"modal-footer\">
                        <button type=\"button\" class=\"btn btn-danger btn-sm\" data-bs-dismiss=\"modal\" id=\"{$domid}_close\">Close</button>
                    </div>";
        if(!$lshowfooter) { 
			$cfooter = "";
		}
        $chtml = "
        <div class=\"modal\" id=\"{$domid}\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdrop_{$domid}\" aria-hidden=\"true\">
            <div class=\"modal-dialog {$cmodaloptions}\">
                <div class=\"modal-content\">
                    <div class=\"modal-header {$cmodalheaderoptions}\">
                        <h5 class=\"modal-title\" id=\"static_title_{$domid}\">{$ctitle}</h5>
                        <button type=\"button\" class=\"btn btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                    </div>
                    <div class=\"modal-body\" id=\"{$domid}_bod\">
                     {$cmsg}
                    </div>
                    {$cfooter}
                </div>
            </div>
        </div>      
        ";
        return $chtml;
    } //end memsgbox2        

    public function memsgbox3($domid,$ctitle='',$cmsg='') { 
        $chtml = "
        <div class=\"modal\" id=\"{$domid}\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdrop{$domid}\" aria-hidden=\"true\">
            <div class=\"modal-dialog\">
                <div class=\"modal-content\">
                    <div class=\"modal-header bg-secondary p-1\" id=\"{$domid}_mebgtitle\">
                        <h4 class=\"modal-title fw-bolder\" id=\"static_title_{$domid}\" style=\"color: white !important;\">{$ctitle}</h4>
                        <button type=\"button\" class=\"btn-close btn-sm\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                    </div>
                    <div class=\"modal-body p-1\" id=\"{$domid}_bod\">
                        {$cmsg}
                    </div>
                </div>
            </div>
        </div>      
        ";
        return $chtml;
    } //end memsgbox3
    
    public function memsgbox_save_and_close_form($domid,$ctitle='',$cmsg='',$cmodaloptions='',$meactionlink='',$meclassform='',$lform=1) {
        $chtml = "
        <div class=\"modal\" id=\"{$domid}\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdrop_{$domid}\" aria-hidden=\"true\">
            <div class=\"modal-dialog {$cmodaloptions}\">" . 
            ($lform ? form_open($meactionlink,' method="post" id="myfrm_' . $domid . '" name="myfrm_' . $domid . '" ' . $meclassform) : '') . "
                <div class=\"modal-content\">
                    <div class=\"modal-header\">
                        <h5 class=\"modal-title\" id=\"static_title_{$domid}\">{$ctitle}</h5>
                        <button type=\"button\" class=\"btn btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                    </div>
                    <div class=\"modal-body\" id=\"{$domid}_bod\">
                     {$cmsg}
                    </div>
                    <div class=\"modal-footer\">
                        <button class=\"btn btn-info btn-sm\" id=\"{$domid}_yes\" >Save</button>
                        <button type=\"button\" class=\"btn btn-danger btn-sm\" data-bs-dismiss=\"modal\" id=\"{$domid}_close\">Close</button>
                    </div>
                </div> " . 
			($lform ? form_close() : '') . " <!-- end of ./form -->
            </div>
        </div>      
        ";
        return $chtml;
    } //end memsgbox_save_and_close
    
    public function bs_toast_alert1($metoastid='',$classtoast='align-items-center text-bg-danger border-0',$cmesg='',$lshowscript=0) { 
		$chtml = "
		<div id=\"memsgmetoast\" class=\"toast {$classtoast}\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\">
			<div class=\"d-flex\">
				<div class=\"toast-body\">
					{$cmesg}
				</div>
				<button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
			</div>
		</div>
		";
		if($lshowscript):
			$chtml .= "
			<script>
				var myAlertToast = document.getElementById('{$metoastid}');
				var bsAlertToast = new window.Toast(myAlertToast); //inizialize it
				bsAlertToast.show(); //show it
			</script>
			";
		endif;
		return $chtml;
	} //end bs_toast_alert
    
    public function bs_toast_alert2($metoastmaincontid='',$metoastid='',$classtoast='align-items-center text-bg-danger border-0',$cmesg='',$lshowscript=0) { 
		$chtml = "
		<div id=\"{$metoastid}\" class=\"toast {$classtoast}\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\">
			<div class=\"d-flex\">
				<div class=\"toast-body\">
					{$cmesg}
				</div>
				<button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
			</div>
		</div>
		";
		if($lshowscript):
			$chtml .= "
			<script>
				var myAlertToast = document.getElementById('{$metoastid}');
				var bsAlertToast = new window.Toast(myAlertToast); //inizialize it
				bsAlertToast.show(); //show it
			</script>
			";
		endif;
		return $chtml;
	} //end bs_toast_alert    
    
    
    
	function formatFileSize($filename) { 
		$size = filesize($filename);

		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$formattedSize = $size;

		for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
			$size /= 1024;
			$formattedSize = round($size, 2);
		}

		return $formattedSize . ' ' . $units[$i];
	} //end formatFileSize

}  //end main MyLibzSysModel
