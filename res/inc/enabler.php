<div class="box box-full"><h2>USB Scanner udev Rule Maker (Automated Script)</h2>
<p>
The <a href="scanner-udev-rule-maker.tar.bz2">Scanner udev Rule Maker</a> is used to grant access to USB scanners that use libusb. It is a script that checks for libusb scanners and changes the permission on it so all users can access it and creates a configuration file so change will be persistent.<br/>
The file it creates is <i>/etc/udev/rules.d/40-scanner.rules</i>, if you already have this file it will give you a comparison of the rule(s) and let you do what you feel you need to (nothing, merge, append, etc.). Most likely it will create the file, if you run it twice it will tell you the file is up to date.<br/>
To use it simply extract the file and run the script as root in a terminal, you can safely delete the script after using it.</p>
<pre>
sudo bash /path/to/scanner-udev-rule-maker
</pre>
</div>

<div class="box box-full" id="rulegen"><h2>USB Scanner udev Rule Maker (Manual Installation)</h2>
<p>
Some scanners are not detected by the 'USB Scanner udev Rule Maker'. This tool is used to generate rules for the administrator to install manually.<br/>
Select the scanners from this list of USB devices then click the 'Generate' button to get the necessary udev rules.</br>
To install these rules save the generated rules to <i>/etc/udev/rules.d/40-scanner.rules</i></p>
<form action="index.php?page=Access%20Enabler" method="POST"><ul class="columns">
<?php
	$usb=explode("\n",exe('lsusb',true));
	foreach($usb as $dev){
		if($dev=='')
			continue;
		$dev=explode("ID ",$dev)[1];
		$id=substr($dev,0,strpos($dev,' '));
		$name=substr($dev,strpos($dev,' ')+1);
		echo '<li><input type="checkbox" name="'.html($id).'" value="'.html($name).'"/>'.html($name).'</li>';
	}
?>
</ul>
<input type="submit" value="Generate"/>
</form>
<?php
	if(count($_POST)>0){
		$group="lp";
		$type="GROUP";
		if(exe("id $group > /dev/null;echo \$?",true)==1){
			$group="scanner";
			if(exe("id $group > /dev/null;echo \$?",true)==1){
				$group="666";
				$type="MODE:";
			}
		}
		$paths=array();
		echo "<div><span>Rules:</span><pre>";
		foreach($_POST as $key => $val){
			foreach($usb as $dev){
				if(strpos($dev,$key)>-1){
					$dev=substr($dev,0,strpos($dev,':'));
					$dev=explode(' ',$dev);
					array_push($paths,$dev[1].'/'.$dev[3]);
					break;
				}
			}
			$key=explode(":",$key);
			$vnd=$key[0];
			$itm=$key[1];
			echo html("# $val\n".
				"SUBSYSTEMS==\"usb\", ATTRS{idVendor}==\"$vnd\", ATTRS{idProduct}==\"$itm\", ENV{libsane_matched}=\"yes\", $type=\"$group\"\n");
		}
		echo "</pre>To apply these permissions without rebooting run these commands as root:<pre>";
		foreach($paths as $val){
			echo 'ch'.($type=="GROUP"?"own root:$group":"mod $group")." /dev/bus/usb/$val\n";
		}
		echo "</pre></div>";
	}
?>
</div>

<div class="box box-full"><h2>Scanner Access Enabler (Quick Start)</h2>
<p>
If a scanner shows with <code>scanimage -L</code> and is not detected by the server scanner the problem is permission.<br/>
To enable access <a href="http://ubuntuforums.org/member.php?u=162029">jhansonxi</a> has developed a application that will enable access a copy is included with the PHP Server Scanner.<br/>
To install it <a href="scanner-access-enabler-<?php echo $SAE_VER; ?>.tar.bz2">download the archive</a> and extract it. Then move the script to <code>/usr/local/bin/scanner-access-enabler</code> and set it for root:root ownership with rwxr-xr-x (0755) permissions.
Then move the desktop menu entry to the <code>/usr/local/share/applications</code> directory with root:root ownership and rw-r--r-- (0644) permissions. The application will now be under System -> Administration in Ubuntu.
Some scanners will need to have this done every time you boot.<br/>
If you have to run it every boot add <code>/usr/local/bin/scanner-access-enabler -s</code> before <code>exit 0</code> in <code>/etc/rc.local</code> on its own line and you are good to go.<br/><br/>
So you just want the terminal commands, I will assume you just opened a terminal and extracted the archive to your desktop</p>
<pre># installs application
sudo mv Desktop/scanner-access-enabler /usr/local/bin
# makes next command work
sudo mkdir /usr/local/share/applications
# add menu entry under the system menu
sudo mv Desktop/scanner-access-enabler.desktop /usr/local/share/applications
# enable scanner(s)
sudo /usr/local/bin/scanner-access-enabler
# re-enable scanners every boot
sudo nano /etc/rc.local
# Add "/usr/local/bin/scanner-access-enabler -s" before "exit 0" on its own line without quotes
# press [ctrl]+[O] then [enter] to save
# press [ctrl]+[X] to exit nano</pre>
</div>

<div class="box box-full"><h2>Scanner Access Enabler (Full Details)</h2>
<p>There is a problem with scanner device permissions on Ubuntu. Regular users (<code>UID&gt;999</code>) can access libsane applications like Xsane and <a href="https://launchpad.net/simple-scan">Simple Scan</a> without problems.  PHP Scanner Server, which is running in Apache as www-data, can't access them without a <code>chmod o+rw</code> on each scanner device.  Nobody seems to know <a href="https://answers.launchpad.net/ubuntu/+question/127223">how the permissions work</a> so this has to be fixed manually in a terminal.  This is not n00b friendly so I created a GUI application that automatically changes the permissions of every scanner device.</p>
<p>The application relies on <a href="http://www.sane-project.org/man/scanimage.1.html"><code>scanimage</code></a> and <a href="http://www.sane-project.org/man/sane-find-scanner.1.html">sane-find-scanner</a> utilities to identify scanner device ports then simply does a <code>chmod</code> against all of them. It supports USB, SCSI, and optionally parallel port (-p parameter) scanners and has been tested against the same ones I used for my <a href="http://jhansonxi.blogspot.com/2010/10/patch-for-linux-scanner-server-v12.html">LSS patch</a>. It uses the same universal dialog code as <a href="http://jhansonxi.blogspot.com/2010/09/webcam-server-dialog-basic-front-end-to.html">webcam-server-dialog</a> so it should work with almost any desktop environment.</p><p>To install first <a href="scanner-access-enabler-1.2.tar.bz2">download the archive</a> and extract the contents. Move the script to "<code>/usr/local/bin/scanner-access-enabler</code>" and set it for root:root ownership with <code>rwxr-xr-x</code> (0755) permissions. Copy the <a href="http://standards.freedesktop.org/desktop-entry-spec/latest/">destop menu entry</a> to the <code>/usr/local/share/applications</code> directory with <code>root:root</code> ownership and <code>rw-r--r--</code> (0644) permissions. You may have to edit the desktop file as it uses gksudo by default. On KDE you may want to change the Exec entry to use <code>kdesudo</code> instead. If you specify the -p option on the Exec line you may have to quote everything after gk/kdesudo. If you don't have one of the GUI dialogue utilities installed and plan on using dialog or whiptail then you need to set "Terminal=true" else you won't see anything.</p><p>On Ubuntu the menu item will be found under System &gt; Administration. If you want users to be able to activate scanners without a password and admin group membership, you can add an exception to the end of "<code>/etc/sudoers</code>" file. Simply run "sudo visudo" and enter the following:</p>
<p><code># Allow any user to fix SCSI scanner port device permissions</code><br/>
<code>ALL ALL=NOPASSWD: /usr/local/bin/scanner-access-enabler *</code></p>
<p>While you can use any editor as root to change the file, visudo checks for syntax errors before saving as a mistake can disable sudo and prevent you from fixing it easily.  If you mess it up, you can reboot and use Ubuntu recovery mode or a LiveCD to fix it.</p><p>Update:  I released v1.1 which adds filtering for "net:" devices from saned connections.  This didn't affect the permission changes but made for a crowded dialog with both the raw and net devices shown.</p>
</div>
<script type="application/javascript">printMsg('Information',"The 'USB Scanner udev Rule Maker' is a intended to replace the 'Scanner Access Enabler'.",'center',-1);</script>
