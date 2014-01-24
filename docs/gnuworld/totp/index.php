<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="./css/smoothness/jquery-ui-1.8.20.custom.css" media="screen" />
  <script type='text/javascript' src='./js/jquery-1.7.2.min.js'></script>
  <script type='text/javascript' src='./js/jquery-ui-1.8.20.custom.min.js'></script> 

  <script type="text/javascript">
  $(document).ready(function(){
  <?php
  for ($i=1; $i<10; $i++)
  {
  echo '$("#accordion'.$i.'").accordion({
			collapsible: true,
			autoHeight: false,
			navigation: true,
			active: false
		});
	';
  }
  ?>

$("#tabs").tabs();
  });
  
  </script>

</head>
<body style="font-size:75%;" id="top">
   <div id="main" class="round roundB" style="width: 900px;">

<div id="tabs">
  <ul>
    <li><a href="#gauth">Google authentificator</a> </li>
    <li><a href="#generator">Generator</a> </li>
  </ul>


<div id="gauth">
<h3>
Installing Google Authenticator
</h3>
<p>If you set up 2-step verification using SMS text message or Voice call and also want to be able to generate codes using the Android, iPhone or a Blackberry, you can use the Google Authenticator app to receive codes even if you don’t have an Internet connection or mobile service.</p>
<p>To set this up, first you need to complete <a href="http://support.google.com/accounts/bin/answer.py?hl=en&amp;answer=185839" target="_blank">SMS/Voice setup</a>. Then, go to the <a href="http://google.com/accounts/SmsAuthConfig" target="_blank">2-step verification settings page</a> and click on <strong>Android</strong>, <strong>Blackberry</strong>, or <strong>iPhone</strong>, and follow the directions for your type of phone explained below.</p>

	<div id="accordion4">
	<h3><a href="#">Android</a></h3>
		<div>
			<p>
<h4>Requirements</h4>
<p>To use Google Authenticator on your Android device, it must be running Android version 2.1 or later.</p>
<h4>Downloading the app</h4>
<ol>
<li>Visit <a href="//play.google.com/">Google Play</a>.</li>
<li>Search for <strong>Google Authenticator</strong>.</li>
<li>Download and install the application.</li>
</ol>
<h4>Setting up the app</h4>
<ol>
<li>If you haven’t already, complete the <a href="//www.google.com/support/accounts/bin/static.py?page=guide.cs&amp;guide=1056283&amp;topic=1056285">SMS/Voice setup</a> and enroll your account in 2-step verification using your phone number.</li>
<li>On your computer, go to the <a href="//google.com/accounts/SmsAuthConfig">2-step verification settings page</a> and click on Android.</li>
<li>On your phone, open the Google Authenticator application.</li>
<li>If this is the first time you have used Authenticator, click the <strong>Add an account</strong> button. If you are adding a new account, choose “Add an account” from the app’s menu.</li>
<li>To link your phone to your account:
<ul>
<li><strong>Using QR code</strong>: Select <strong>Scan account barcode</strong> (label 1a). If the Authenticator app cannot locate a barcode scanner app on your phone, you might be prompted to download and install one. If you want to install a barcode scanner app so you can complete the setup process, press <strong>Install</strong> (label 2a) then go through the installation process. Once the app is installed, reopen Google Authenticator, point your camera at the QR code on your computer screen.</li>
<li><strong>Using secret key</strong>: Select <strong>Manually add account</strong> (label 1b), then enter the email address of your Google Account in the box next to <strong>Enter account name</strong> (label 2b). Next, enter the secret key on your computer screen into the box under <strong>Enter key</strong> (label 2c). Make sure you've chosen to make the key <strong>Time based</strong> (label 2d) and press "Save."</li>
</ul>
<br>
<img src="//www.google.com/help/hc/images/accounts-2step-android-initial.gif" alt="Android scan or manual"> <img src="//www.google.com/help/hc/images/accounts-2step-android-install.gif" alt="Android barcode scanner prompt"> <img src="//www.google.com/help/hc/images/accounts-2step-android-manual.gif" alt="Android manual entry"></li>
<li>To test that the application is working, enter the verification code on your phone (label 3) into the box on your computer next to <strong>Code</strong>, then click "Verify."<br>
<br>
<img src="//www.google.com/help/hc/images/accounts-2step-android-code.gif" alt="Android verification code"></li>
<li>If your code is correct, you will see a confirmation message. Click "Save" to continue the setup process. If your code is incorrect, try generating a new verification code on your phone, then entering it on your computer. If you’re still having trouble, you might want to verify that the <a target="_blank" href="http://www.google.com/search?q=local+time">time on your phone is correct</a> <img src="//services.google.com/images/adwords/doit.gif" alt="new window" class=" dmczwokalozdwltfskzi dmczwokalozdwltfskzi"> or read about <a href="answer.py?answer=185834">common issues</a>.</li>
</ol>
			</p>
			<a href="#top">Top</a>
		</div>
	</div>
<br>
	<div id="accordion5">
	<h3><a href="#">iPhone, iPod Touch, or iPad:</a></h3>
 		<div>
 			<p>

<h4>Requirements</h4>
<p>To use Google Authenticator on your iPhone, iPod Touch, or iPad, you must have iOS 3.1.3 or later. In addition, in order to set up the app on your iPhone using a QR code, you must have a 3G model or later.</p>
<h4>Downloading the app</h4>
<ol>
<li>Visit the App Store.</li>
<li>Search for <strong>Google Authenticator</strong>.</li>
<li>Download and install the application.</li>
</ol>
<h4>Setting up the app</h4>
<br>
<br>
<img src="//www.google.com/help/hc/images/accounts-2step-iphone-code.gif" alt="iPhone verification code">
<ol>
<li>If you haven’t already, complete the <a href="//www.google.com/support/accounts/bin/static.py?page=guide.cs&amp;guide=1056283&amp;topic=1056285">SMS/Voice setup</a> and enroll your account in 2-step verification using your phone number. You can add the Google Authenticator app <strong>only after</strong> you’ve already enrolled using your phone number.</li>
<li>On your computer, go to the <a href="//google.com/accounts/SmsAuthConfig">2-step verification settings page</a> and click on iPhone.</li>
<li>On your phone, open the Google Authenticator application.</li>
<li>Tap the plus icon.</li>
<li>Tap <strong>Time Based</strong> (label 1).</li>
<li>To link your phone to your account:
<ul>
<li><strong>Using QR code</strong>: Tap "Scan Barcode" (label 2a) and then point your camera at the QR code on your computer screen.</li>
<li><strong>Using secret key</strong>: In the box next to <strong>Account</strong> (label 2b), enter the email address of your Google Account. Then, enter the secret key on your computer screen into the box next to <strong>Key</strong> (label 2c) and tap "Done" (label 2d).</li>
</ul>
<br>
<img src="//www.google.com/help/hc/images/accounts-2step-iphone-key.gif" alt="iPhone key entry"></li>
<li>To test that the application is working, enter the verification code on your phone (label 3) into the box on your computer next to Code, then click "Verify." The clock icon on your phone (label 4) will let you know how much time is left before the verification code expires and a new one is generated.</li>
<li>If your code is correct, you will see a confirmation message. Click "Save" to confirm. If your code is incorrect, try generating a new verification code on your phone, then entering it on your computer.. If you’re still having trouble, you might want to verify that the <a target="_blank" href="http://www.google.com/search?q=local+time">time on your phone is correct</a> <img src="//services.google.com/images/adwords/doit.gif" alt="new window" class=" dmczwokalozdwltfskzi dmczwokalozdwltfskzi"> or read about <a href="answer.py?answer=185834">common issues</a>.</li>
</ol>

  			</p>
  			<a href="#top">Top</a>
 		</div>
	</div>
<br>





</div>
<div id="generator">
<iframe src="qrgen.php?code=<?php echo $key;?>&name=UnderNet" frameborder="0" scrolling="no" width="100%"/>
</div>

</div>
</body>
</html>
