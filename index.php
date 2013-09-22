<?php          
require_once('CurlBrowser.php');
    session_start();
    if ($_POST != null){
        if($_POST['act'] == "save"){
            file_put_contents('cases.txt', implode("\n", $_SESSION['cases'])); 
            exit;  
        }
        $browser = $_SESSION["browser"];
        $url = "https://mycase.in.gov/Search.aspx?ID=200&NodeID=9301&NodeDesc=Marion%20County%20-%20Center%20Township";
        $year = $_POST["year"];
        $no = $_POST["no"];
        $month = $_POST["month"];
        $j = $month;        
        for($j = $month;$j<13;$j++)
        {
            $year_str = str_pad((int)$year,2,"0",STR_PAD_LEFT);;
            $month_str = str_pad((int)$j,2,"0",STR_PAD_LEFT);
            $no_str = str_pad((int)$no,5,"0",STR_PAD_LEFT);
            $caseNo = "49k01-".$year_str.$month_str."-sc-".$no_str;            
            $data = "__EVENTTARGET=&__EVENTARGUMENT=&SearchBy=0&ExactName=on&CaseSearchMode=CaseNumber&CaseSearchValue=".$caseNo."&CitationSearchValue=&CourtCaseSearchValue=&PartySearchMode=Name&AttorneySearchMode=Name&LastName=&FirstName=&cboState=AA&MiddleName=&DateOfBirth=&DriverLicNum=&CaseStatusType=0&DateFiledOnAfter=1%2F1%2F2002&DateFiledOnBefore=&chkCriminal=on&chkFamily=on&chkCivil=on&chkProbate=on&chkDtRangeCriminal=on&chkDtRangeFamily=on&chkDtRangeCivil=on&chkDtRangeProbate=on&chkCriminalMagist=on&chkFamilyMagist=on&chkCivilMagist=on&chkProbateMagist=on&DateSettingOnAfter=&DateSettingOnBefore=&SortBy=fileddate&SearchSubmit=Search&SearchType=CASE&SearchMode=CASENUMBER&NameTypeKy=&BaseConnKy=&StatusType=true&ShowInactive=&AllStatusTypes=true&CaseCategories=&RequireFirstName=&CaseTypeIDs=&HearingTypeIDs=&SearchParams=SearchBy%7E%7ESearch+By%3A%7E%7ECase%7E%7ECase%7C%7CchkExactName%7E%7EExact+Name%3A%7E%7Eon%7E%7Eon%7C%7CCaseNumberOption%7E%7ECase+Search+Mode%3A%7E%7ECaseNumber%7E%7ENumber%7C%7CCaseSearchValue%7E%7ECase+Number%3A%7E%7E".$caseNo."%7E%7E".$caseNo."%7C%7CAllOption%7E%7ECase+Status%3A%7E%7E0%7E%7EAll%7C%7CDateFiledOnAfter%7E%7EDate+Filed+On+or+After%3A%7E%7E1%2F1%2F2002%7E%7E1%2F1%2F2002%7C%7CselectSortBy%7E%7ESort+By%3A%7E%7EFiled+Date%7E%7EFiled+Date";            
            $data = $data.$_SESSION['postdata'];
            $result = $browser->post($url,$data);
            $pattern = '/href="CaseDetail\.aspx\?CaseID=.*">(.*)<\/a>/';
            preg_match($pattern,$result,$matches);
            if(count($matches) != 0){                
                $month = $j;                                  
                array_push( $_SESSION['cases'],$caseNo);
                $result = Array("caseNo" => $caseNo,"month" => $month);
                echo json_encode($result);
                exit;
            }            
        }
        file_put_contents('cases.txt', implode("\n", $_SESSION['cases']));
    }
    else{             
        $browser = new CurlBrowser();        
        $_SESSION["browser"] = $browser;
        $_SESSION['cases'] = Array();        
        $url = "https://mycase.in.gov/login/default.aspx";
        $result = $browser->get($url); 
        $ny = date("Y")-2000; 
        $url = "https://mycase.in.gov/Search.aspx?ID=200&NodeID=9301&NodeDesc=Marion%20County%20-%20Center%20Township";
        $result = $browser->get($url);        
        $pattern1 = '/<input type=\\"hidden\\" name=\\"__VIEWSTATE\\" id=\\"__VIEWSTATE\\" value=\\"(.*)" \/>/';
        preg_match($pattern1,$result,$matches);
        $viewstate = $matches[1];
        $pattern2 = '/<input type=\\"hidden\\" name=\\"__EVENTVALIDATION\\" id=\\"__EVENTVALIDATION\\" value=\\"(.*)" \/>/';
        preg_match($pattern2,$result,$matches);
        $evalid = $matches[1];
        $_SESSION['postdata'] = http_build_query(array('__VIEWSTATE'=>$viewstate,'__EVENTVALIDATION'=>$evalid));      
?>
<!DOCTYPE html>
<html>
  <head>        
    <script type="text/javascript" src="jquery-1.9.1.js"></script>
  </head>
  <body>
<input type="button" name="submit" value="get cases"><br>
<script>    
$("input[name = 'submit']").click(function(){
    var ny = <?php echo $ny;?>;
    var month = 1;    
    for(year=2;year<=ny;year++){
        for(no=1;no<99999;no++){            
            $.ajax({
                url:"",
                type:"post",
                dataType:"json",
                async:false,
                data:{"act":"get","year":year,"no":no,"month":month},
                success:function(result){                                        
                    if(result != null){
                        month = result.month;
                        $("<span>"+result.caseNo+'<br>'+"</span>").appendTo("body");    
                    }
                }
            });
        }
    }
    $.ajax({
       url:"" ,
       type:"post",
       data:{"act":"save"},
       success:function(){
            alert("Great")   ;
       }       
    });    
});    
</script>
<?php
    }
?>