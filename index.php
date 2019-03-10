<?php
include_once("config.php");
$sql = "SELECT * FROM c_log";
$result = $conn->query($sql);

/*if ($result->num_rows > 0) {
  
    while($row = $result->fetch_assoc()) {
     print_r($row);
    }
} else {
    echo "0 results";
}
$conn->close();*/


include_once("simple_html_dom.php");
function check_domain_exist($url)
{
	$ch = curl_init("http://api.whoapi.com/?apikey=7925383caacdb6abb42ab28b83cf14bb&r=whois&domain=".urlencode($url)."&ip=");
	
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_POST, 1);
	$post = ['domain' => $url];	
	
	curl_setopt($ch, CURLOPT_POSTFIELDS, "domain=".$url."&customer=");
	//array('domain'=>$url)
	
	$output = curl_exec($ch);
	
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	$results	=	json_decode($output,true);			
	#print "<pre>";print_r($results['registered']);
	if($results['registered']=="1")
	{
		return "No";
	}
	else
	{
		return "Yes";
	}
			

}

function get_status($url)
{
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	$output = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $httpcode;
	
}

function check_http_exist_in_url($url) 
{
	$temp	=	explode("://",$url);
	if($temp[0]=="http" or $temp[0]=="https")
	{
		return true;
	}
	else
	{
		return false;
	}
}
function url_to_domain($url)
{
    $host = @parse_url($url, PHP_URL_HOST);
   
    if (!$host)
        $host = $url;
   
    if (substr($host, 0, 4) == "www.")
        $host = substr($host, 4);
 
    if (strlen($host) > 50)
        $host = substr($host, 0, 47) . '...';
    return $host;
}
if(@$_GET['save_action']=="true")
{
	header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=abc.xls");  //File name extension was wrong
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	$data		 =	$_SESSION['result'];
	$http_status =	$_SESSION['http_status']; 	

	?>
	<table class="table table-bordered">
		<tr>
			<th nowrap="nowrap">Broken Link</th>
			<th nowrap="nowrap">Found On Page</th>
			<th nowrap="nowrap">Domain Avaliable</th>
			<?php if($http_status="Yes"){?>
			<th nowrap="nowrap">Http Status</th>
			<?php }?>
		</tr>
		<?php foreach($data as $val){?>
		<tr>
			<td><?php echo $val['found_url']?></td>
			<td><?php echo $val['main_url']?></td>
			<td><?php echo $val['domain_avaliable']?></td>
			<?php if($http_status="Yes"){?>
			<td><?php echo $val['status']?></td>
			<?php }?>
		</tr>
		<?php }?>
	</table>
	<?php
	exit;
	
}
if(@$_POST['action']=="start" and @$_POST['url']!="")
{
	$html = new simple_html_dom();
 
	// Load from a string
	$urlm		=	$_POST['url'];
	$url_detail	=	url_to_domain($urlm);
	
	$ch = curl_init($urlm);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	if(!$_POST['proxy']!="")
	{
		curl_setopt($ch, CURLOPT_PROXY, $_POST['proxy']);
	}
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	$contents = curl_exec($ch);
	$redirectURL = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL );
	curl_close($ch);

	$html->load($contents);
	$url	=	array();	
	foreach($html->find('a') as $e)
	{	
		$url_detail2	=	url_to_domain($e->href);
		if($url_detail!=@$url_detail2)
		{
			if($e->href!="" and check_http_exist_in_url($e->href) )
			{
				$url[]	= $e->href;	
			}
		}
	}
	$data	=	array();		
	$s	=	0;
	foreach($url as $val)
	{
		if($s<3 or 1)
		{
			$domain_avaliable	=	"Yes";
			$status				=	get_status($val);
			if($status!="200")
			{
				$domain_avaliable	=	check_domain_exist($val);
			}
			
			$data[]	= array('main_url'=>$urlm,'found_url'=>$val,'status'=>$status,'domain_avaliable'=>$domain_avaliable);
		}
		$s++;
	}
	$http_status="Yes";
	if(@$_POST['http_status']=="No")
	{
		$http_status	=	"No";
	}
	$_SESSION['result']		 =		$data;
	$_SESSION['http_status'] =	$http_status;	
	#print "<pre>";print_r($data);
	?>
	<table class="table table-bordered">
		<tr>
			<th nowrap="nowrap">Broken Link</th>
			<th nowrap="nowrap">Found On Page</th>
			<th nowrap="nowrap">Domain Avaliable</th>
			<?php if($http_status="Yes"){?>
			<th nowrap="nowrap">Http Status</th>
			<?php }?>
		</tr>
		<?php foreach($data as $val){?>
		<tr>
			<td><?php echo $val['found_url']?></td>
			<td><?php echo $val['main_url']?></td>
			<td><?php echo $val['domain_avaliable']?></td>
			<?php if($http_status="Yes"){?>
			<td><?php echo $val['status']?></td>
			<?php }?>
		</tr>
		<?php }?>
	</table>
	<?php
	exit;
}

?>
<html>
	<head>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/custom.css">
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		
		$(".start").click(function(){
			
			 var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))'+ // ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ //port
            '(\\?[;&amp;a-z\\d%_.~+=-]*)?'+ // query string
            '(\\#[-a-z\\d_]*)?$','i');
			
			if($("#url").val()=="")
			{
				alert("Please Enter Url");
				return false;
			}
			if (!pattern.test($("#url").val())) {
				alert('Invalid URL-- missing "http://" or "https://"');
				return false;
			}
			
			$(".response").html("<img src='images/waiting.gif'>");
			$.ajax({
				url:'index.php',
				method:'POST',
				data:$(".my_form").serialize(),
				success:function(data)
				{
					$(".response").html(data);
				}
			
			});
		});
		$(".stop").click(function(){
			
			window.location.href=window.location.href;
		});
		$(".setting").click(function(){
				$(".setting_cont").show();
				$(".main_cont").hide();
				$(".response").hide();
		});
		$(".save_setting").click(function(){
			if($("#http_status").val()!="")
			{
				if($("#http_status").val()!="No" && $("#http_status").val()!="Yes")
				{
					alert(" Http Status Should be Yes or Not or empty for Yes");
					return false;
				}
			}
			if($("#check_domain_av").val()!="")
			{
				if($("#check_domain_av").val()!="No" && $("#check_domain_av").val()!="Yes")
				{
					alert(" Check Domain Should be Yes or Not or empty for Yes");
					return false;
				}
			}
			
			$(".setting_cont").hide();
			$(".main_cont").show();
			$(".response").show();
			
		});
		
	
	});
	</script>
	</head>
	<body>
		<div class="container">
			<form class="my_form form " action="#">
			  <div class="row main_cont">
				<div class=" col-md-4">
					<input  type="hidden" name="action" value="start">
				  <input type="text" class="form-control" name="url" id="url" placeholder="Enter Url"  value="https://www.forbes.com/sites/allbusiness/2018/12/09/6-common-business-startup-mistakes/#6903a06c331f">
				</div>
				<div class=" col-md-2">
					<button type="button" class="btn btn-success start full_width">Start</button>
				</div>
				<div class=" col-md-2">
					<button type="button" class="btn btn-danger stop full_width">Stop</button>
				</div>
				<div class=" col-md-2">
					<a type="button" class="btn btn-info save full_width" href="index.php?save_action=true">Save</a>
				</div>
				<div class=" col-md-2">
					<button type="button" class="btn btn-default setting full_width">Setting</button>
				</div>
			  </div>
			  <div class="setting_cont" style="display:none">
				<div class=" col-md-12 setting_panel">
				  <input type="text" class="form-control" name="proxy" id="proxy" placeholder="Proxy"  value="">
				</div>
				<div class=" col-md-12 setting_panel">
				  <input type="text" class="form-control" name="http_status" id="http_status" placeholder="Http Status Yes OR No By Default Yes"  value="">
				</div>
				<div class=" col-md-12 setting_panel">
				  <input type="text" class="form-control" name="check_domain_av" id="check_domain_av" placeholder="check domain Yes OR No By Default Yes"  value="">
				</div>
				<div class=" col-md-2 setting_panel">
					<button type="button" class="btn btn-info save_setting full_width">save Setting</button>
				</div>
			  </div>
			</form>
			<div class="response"></div>
		</div>
	</body>	
</html>