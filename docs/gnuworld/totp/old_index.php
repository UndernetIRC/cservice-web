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
    <li><a href="#intro">Intro</a> </li>      
    <li><a href="#usage">Usage</a> </li>
    <li><a href="#gauth">Google authentificator</a> </li>
    <li><a href="#generator">Generator</a> </li>
  </ul>

<div id="intro">
Hi All,<br />
<br />
I have several main agendas on my horizon for improvements in the CService world, for Undernet.<br />
<br />
The general focus I have at the moment revolves around:<br />
<br />
- Protection (Security &amp; improved audits of actions). We get harassed and attacked, and so do our users. How can we improve security and general protection?<br />
<br />
- Efficiency &amp; Ease of Use (For our functions, and for users on the network). What can make our lives easier, and how can we make it so we're easier to deal with?<br />
<br />
- Innovation. What can we do that sets Undernet apart to retain users, and even gain new ones?<br />
<br />
<br />
I've given a lot of thought around these areas in recent months, and want to specifically discuss one of them with you in this thread. Others will come later, as separate threads, so please stick to the topic.<br />
<br />
This, relates to security &amp; protection -- specifically, authentication.<br />
<br />
In the electronic world, it has been widely accepted for many years that the best form of security is formed around a concept of "what you know, what you are, and what you have". In electronic access control, this often means "a PIN number, a biometric template (ie. fingerprint), and a security access card". In order to compromise security, all factors must be provided.<br />
<br />
We, currently employ only one of those factors for our users, being username+password ('what you know'). For CService staff, we also have IPR at our disposal, which does introduce the 'what you are' aspect, being your IP address. Unfortunately, it doesn't help when some people have very wide IPR masks, and it doesn't help if someone actually compromises your PC, and sniffed your password. IPR also, isn't something that is easily managed for our userbase, which is why it doesn't exist for them.<br />
<br />
So, I'd like to propose that we introduce another factor.<br />
<br />
Many of you will have seen the surge in prevalence of multi-factor authentication on websites in recent years. Commonly, the 'what you have', is a mobile device. You login to the website with a username and a password, and then the site will send you an SMS with a token, to then enter in addition, to authenticate with the site. If someone gets your credentials and doesn't physically have your mobile phone, they can't get in. This is also achieved by using physical tokens (such as the RSA ones) -- which have rolling tokens on an LCD screen.<br />
<br />
The use of SMS messages isn't really ideal for us because it involves costs (or significant sponsorship), and an API to external servers.<br />
<br />
Some years ago, industry leaders got together and formed OATH - Initiative For Open Authentication (http://www.openauthentication.org), this was to collaborate with a goal of industry standards for improved authentication. Some of these members, have worked on standards for One Time Passwords (OTP). There are published documents for algorithms that are used, to manage OTP's. Two widely adopted ones, are HMAC-based One-Time Passwords (HOTP), and later, Time-based One-Time Passwords (TOTP). You'll see folk like Google, Facebook, Microsoft etc who have adopted TOTP.<br />
<br />
The purpose of this e-mail, is to discuss to more recent and secure of these - TOTP. If you'd like to do more in depth reading on this, I'd welcome you to read a general intro at http://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm and an in depth specification at http://tools.ietf.org/html/rfc6238 <br />
<br />
Essentially what happens, is a user has a secret key generated, which is stored in a database against their username. They also record that key, in a device such as their mobile phone or a physical hardware token that supports TOTP. This key, is only seen once, and is then securely stored.<br />
<br />
When someone attempts to authenticate, they use their username and password. They are then asked for a 6 digit token, which is generated by their device which holds their unique key. The TOTP algorithm basically hashes the key against the time, and truncates the result to produce a 6 digit token. The users enters this on the website, and the site does the same calculation. If they match, they are granted access. If not, they are denied.
<br><br>
	<div id="accordion1">
	<h3><a href="#">Some notes:</a></h3>
 		<div>
 			<p>
 - The tokens are only valid for a short period, so in this instance, they are one time passwords.<br />
- All major mobile phone OS' have applications (in lots of instances, several different apps), that support TOTP generation. Search your favourite app store for 'TOTP'<br />
- There are physical tokens such as yubikey (http://www.yubico.com/yubikey) that can support this with a USB keyring style device, should the user not have a mobile phone, but still want to utilise the security feature.<br />
- In the case of mobile phones, there needn't be any data connection or communication with any external device to generate the tokens. The authentication server needn't communicate with anything else either.
  			</p>
  			<a href="#top">Top</a>
 		</div>
	</div>
<br>
I'd like to propose, that we introduce this on Undernet, for both online X authentication, and also the website.  It could be optional per user.
<br><br>
	<div id="accordion2">
	<h3><a href="#">What I'd foresee as a process:</a></h3>
		<div>
			<p>
1). User logs into the website with username and pass (ie. as currently).
2). User opts to enable OTP security
3). Website generates a unique secret key for user (minimum 128 bits).  Website stores in SQL DB against user.  
4). User stores this secret key in physical device (ie. mobile phone app, USB token).  User only sees this key on website once (but could re-generate a new one, if logged in).
			</p>
			<a href="#top">Top</a>
		</div>
	</div>
<br>
Once the user has this enabled, they will be asked for the TOTP token after successfully matching username+password(+IPR), before authentication will be successful. X and the website, can run the matching algorithm on their respective ends.<br />
<br />
For X, it could respond if TOTP is enabled, asking for a second message with the token. Or users could even include it in the same message (where last word is token): /msg x@channels.undernet.org login &lt;user&gt; &lt;pass&gt; &lt;token&gt;. Failing to meet the syntax would result in an appropriate response. Exact implementation would be on the coding side.<br />
<br />
LoC (login-on-connect) -- ie. authenticating with X as you connect to the server instead of after (and setting umode +x immediately), has been in development for a while. TOTP can be integrated in this method as well as the others.<br />
<br />
The use of TOTP means that we adopt strengthened security for authentication:<br />
<br />
- 'what you know' = username + password<br />
- 'what you are' = IPR (for staff)<br />
- 'what you have' = physical device<br />
<br />
This combination protects us, and ultimately our users from hacked passwords, and the fall out in a lot of cases, from hacked machines.<br />
<br />
I would propose, that we firstly adopt this for CService staff, and Opers. If it proves successful and we can address the management side of things comfortably, I'd propose we then offer it to all users, as an *optional* security feature. The help page/document for it would be simple, and User-Com can run classes.
<br><br>
	<div id="accordion3">
	<h3><a href="#">Some of the things we would need to work out when pushing out to users in particular are:</a></h3>
		<div>
			<p>
 1). What happens if someone loses their secret key? Or their device that stored it? Or they want a new device to generate the tokens. If they're not currently logged into the site, they can't generate a new secret key. So, we need a process for staff to handle this. I'd envisage process similar to what the x@ team does now with change or loss of details. We need to identify them, and disable OTP so they can login with username+password, then generate a new key, and re-enable TOTP should they want to.<br />
<br />
2). What happens if someone says they have lost their secret key AND password? This obviously needs to be treated more carefully than the above scenario. And we'd want more stringent checks to identify the user. Possibly we should introduce some more VA options, as an idea?<br />
<br />
To cater to #1 and #2 above, it would make the x@ team busier. We'd probably need more resources there. I'm keen to hear from this team about how these processes could work.<br />
<br /> 
			</p>
			<a href="#top">Top</a>
		</div>
	</div>
<br><br>
A TOTP implementation involves coding and I've had some discussions with some key people on this already. I really wanted to now bring it to senior admins to discuss the general idea, and possible processes for secret key management on the website.<br />
<br />
This e-mail ended up longer than I expected but I wanted it to be thorough so the whole idea was properly understood.<br />
<br />
Now, let's hear your feedback :&gt;<br />
<br />
Cheers,<br />
<br />
- Empus

</div>

<div id="usage">
First step, enabling TOTP. Syntax is not shown as not [yet] available to all users.<br />
<br />
[msg(x)] set totp<br />
-X(cservice@undernet.org)- SYNTAX: SET [#channel] &lt;variable&gt; &lt;value&gt; or, SET &lt;invisible&gt; &lt;ON|OFF&gt; or, SET LANG &lt;language&gt; or, SET MAXLOGINS &lt;max-logins&gt;.<br />
<br />
<br />
<br />
Obtain secret key, shown in multiple formats.<br />
TOTP is not activated until confirmation of successful device setup has been received.<br />
<br />
[msg(x)] set totp on<br />
-X(cservice@undernet.org)- TOTP key set. Your base 32 encoded secret key is: LRWGA7KVGVIDCXJUJZFTOKBDNVVWW53P<br />
-X(cservice@undernet.org)- Your key in hex string: 5c6c607d553550315d344e4b3728236d6b6b776f<br />
-X(cservice@undernet.org)- For QR representation of your key, visit : https://cservice.undernet.org/genqr.php?code=LRWGA7KVGVIDCXJUJZFTOKBDNVVWW53P&amp;name=UnderNet<br />
-X(cservice@undernet.org)- Please note, this key will never be presented to you again. NEVER GIVE YOUR KEY TO ANYONE!<br />
-X(cservice@undernet.org)- To confirm TOTP activation please configure your device with the above key and type: /msg X set TOTP confirm &lt;token&gt;<br />
<br />
<br />
<br />
Users cannot disable TOTP themselves. Currently requires CService coordinator action.<br />
<br />
[msg(x)] set totp off<br />
-X(cservice@undernet.org)- Invalid option.<br />
<br />
<br />
<br />
Before TOTP activation has been confirmed, users can re-generate new secret keys as many times as they like<br />
<br />
[msg(x)] set totp on<br />
-X(cservice@undernet.org)- TOTP key set. Your base 32 encoded secret key is: NI3TMPRMOR3CISDYHMTS4RL3IJQVG4ZD<br />
-X(cservice@undernet.org)- Your key in hex string: 6a37363e2c74762448783b272e457b4261537323<br />
-X(cservice@undernet.org)- For QR representation of your key, visit : https://cservice.undernet.org/genqr.php?code=NI3TMPRMOR3CISDYHMTS4RL3IJQVG4ZD&amp;name=UnderNet<br />
-X(cservice@undernet.org)- Please note, this key will never be presented to you again. NEVER GIVE YOUR KEY TO ANYONE!<br />
-X(cservice@undernet.org)- To confirm TOTP activation please configure your device with the above key and type: /msg X set TOTP confirm &lt;token&gt;<br />
<br />
<br />
<br />
TOTP is not shown to CService staff until TOTP activation has been confirmed:<br />
<br />
[msg(x)] info empus<br />
-X(cservice@undernet.org)- Information about: Empus (1331191)<br />
-X(cservice@undernet.org)- Currently logged on via:<br />
-X(cservice@undernet.org)- Empus!empus@London.UK.Eu.UnderNet.org<br />
-X(cservice@undernet.org)- Flags set: INVISIBLE NOADDUSER NOPURGE LANG=EN MAXLOGINS=3 INVITE<br />
-X(cservice@undernet.org)- LAST SEEN: 0 days, 00:01:46 ago.<br />
... etc<br />
<br />
<br />
<br />
TOTP activation warning, one last step before activating one time passwords:<br />
<br />
[msg(x)] set totp confirm 662273<br />
-X(cservice@undernet.org)- WARNING: This will enable time-based OTP (one time passwords). Once enabled, in order to login you will require a device to generate the OTP token which has the stored secret key. If you are sure, type: /msg X set totp CONFIRM &lt;token&gt; -force<br />
<br />
<br />
<br />
TOTP activation confirmed with valid token generated by OTP device:<br />
<br />
[msg(x)] set totp confirm 662273 -force<br />
-X(cservice@undernet.org)- TOTP Authentication is ENABLED<br />
<br />
<br />
TOTP now shown via 'INFO' command to CService staff:<br />
<br />
[msg(x)] info empus<br />
-X(cservice@undernet.org)- Information about: Empus (1331191)<br />
-X(cservice@undernet.org)- Currently logged on via:<br />
-X(cservice@undernet.org)- Empus!empus@London.UK.Eu.UnderNet.org<br />
-X(cservice@undernet.org)- Flags set: INVISIBLE NOADDUSER NOPURGE LANG=EN MAXLOGINS=3 INVITE TOTP<br />
-X(cservice@undernet.org)- LAST SEEN: 0 days, 00:09:29 ago.<br />
... etc<br />
<br />
<br />
Authentication Attempt: Incorrect password, no TOTP token<br />
<br />
[msg(x@channels.undernet.org)] login empus wrongPass<br />
-X(cservice@undernet.org)- AUTHENTICATION FAILED as empus. (Missing TOTP token)<br />
<br />
<br />
Authentication Attempt: Incorrect password, invalid TOTP token<br />
<br />
[msg(x@channels.undernet.org)] login empus wrongPass 123456<br />
-X(cservice@undernet.org)- AUTHENTICATION FAILED as Empus (Invalid Password)<br />
<br />
<br />
<br />
Authentication Attempt: Correct password, no TOTP token<br />
<br />
[msg(x@channels.undernet.org)] login empus temPass<br />
-X(cservice@undernet.org)- AUTHENTICATION FAILED as empus. (Missing TOTP token)<br />
<br />
<br />
<br />
Authentication Attempt: Correct password, invalid TOTP token<br />
<br />
[msg(x@channels.undernet.org)] login empus temPass 123456<br />
-X(cservice@undernet.org)- AUTHENTICATION FAILED as Empus. (Invalid Token)<br />
<br />
<br />
<br />
Authentication Attempt: Correct password, valid TOTP token<br />
<br />
[msg(x@channels.undernet.org)] login empus temPass 137756<br />
-X(cservice@undernet.org)- AUTHENTICATION SUCCESSFUL as Empus<br />
<br />
<br />
<br />
Authenticated users, cannot disable TOTP thesmelves whilst authenticated. Currently requires override from CService Coordinator.<br />
<br />
[msg(x)] set totp off<br />
-X(cservice@undernet.org)- Invalid option.
</div>

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
<iframe src="qrgen.php" frameborder="0" scrolling="no" width="100%"/>
</div>

</div>
</body>
</html>
