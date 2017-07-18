   _   _   _   _   _     _   _   _   _   _   _     _   _   _   _	
  / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \	
 ( U | - | 2 | 3 | 2 )-( S | O | U | R | C | E )-( C | O | D | E )	
  \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/	
  
|--------------------------------------------------------------------|
|	Credits
|--------------------------------------------------------------------|

		All Credit goes to the original code creators, especially to any author for the mods i selected for U-232.

		The original coders of torrentbits and especially to CoLdFuSiOn for carrying on the legacy with Tbdev.

		The coders of gazelle for the class.cache, sctbdev for various replacement code.
		
		All other mods and snippets for this version from CoLdFuSiOn, putyn, pdq, djGrrr, Retro, elephant, ezero, Alex2005, system, sir_Snugglebunny, laffin, Wilba, Traffic, dokty, djlee, neptune, scars, Raw, soft, jaits, Melvinmeow, RogueSurfer, stoner, Stillapunk, swizzles, autotron. 
		
		U-232 wants to thank everyone who helped make it what it is today; shaping and directing our project, all through the thick and thin. It wouldn't have been possible without you. This includes our users and especially Beta Testers - thanks for installing and using u-232 source code as well as providing valuable feedback, bug reports, and opinions.

|--------------------------------------------------------------------|
|	The Team
|--------------------------------------------------------------------|

		Lead Coders
			Mindless, putyn
			
		Coders
			Stillapunk, autotron

		Support Specialists
			Credit's to pdq/putyn for improvements in key areas on the code. Your input has been first class.
		
		Lead Designer
			RogueSurfer
			
		Designers
			Credit's to Kidvision & others for designs used in the v0+v1+v2 Installer projects.
			Credit's to Roguesurfer for all v3&v4 design - Your a credit to this team.
			Credit's to swizzles for his work on framework intergration and design layout for v4.

|--------------------------------------------------------------------|
|	Special Thanks
|--------------------------------------------------------------------|

		Consulting Developers
			Huge thanks to pdq for so much input and improved code and guidance with memcache.
		
		Beta Testers
			The invaluable few who tirelessly find bugs, provide feedback, and drive the developers crazier. 
			
		Language Translators
			Thank you for your efforts which make it possible for people all around the world to use U-232. 

		Founding Father
			Bigjoos 
		
		Original Project Managers
			Bigjoos, putyn, kidvision 

	THERES TO MANY TO MENTION HERE BUT THE UPMOST RESPECT AND CREDIT TO YOU ALL.

|--------------------------------------------------------------------|
|	Support Forum
|--------------------------------------------------------------------|

		forum.u-232.com 

|--------------------------------------------------------------------|
|	Set Up Instructions:
|--------------------------------------------------------------------|

	Please take note;
	Before you begin installation it is very important that your server is configured correctly and has all the required source code dependencies.

		Ensure your error reporting is enabled on the server and you are logging the errors and not just displaying them.
		A error on install is a failure to adhere to setup instructions.
		If you experience a failure then a properly configured server will report that issue, no excuses required.

		1. Create a directory one up from root so it resides beside it not inside it, named bucket.
			Then inside the bucket folder make another and name it avatar.
			If you use your own names for those folders then you need to edit bitbucket.php and img.php defines at top of the files.
			Then add a .htaccess and index.html files into both newly created folders.
			Then chmod those above folders.
			Then extract pic.rar - javairc.rar and GeoIp.rar and ensure they are not inside an extra folder from being archived.
			Then upload the code to your server and chmod; 
				- cache and all nested files and folders 
				- dir_list 
				- uploads 
				- uploadsubs 
				- imdb imdb/cache 
				- imdb/images 
				- include 
				- include/backup 
				- include/settings settings.txt 
				- install/config.sample.php 
				- install/ann_config.sample.php 
				- logs 
				- torrents


		2. Create a new database user and password via phpmyadmin.

		3. Point to https://yoursite.com/install/index.php - fill in all the required data - then log in. 

		4. Create a second user on entry named System ensure its userid2 so you dont need to alter the autoshout function on include/user_functions.php. 

		5. Sysop is added automatically to the array in cache/staff_settings.php and cache/staff_setting2.php.

		6. Staff is automatically added to the same 2 files, but you have to make sure the member is offline before you promote them.
