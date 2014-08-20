<?php
// Install actions
$rootpath = "../"; require_once $rootpath."includes/error.php";

switch($_REQUEST['action']) {

	case "directories":

		$dirs = "";

		$dir = array('tmp', 'cache', 'data', 'config', 'views/images', 'includes/upload');
		foreach($dir as $d => $dn) {

			$dirs .= '<span style="color:#003366">'.$dn.': </span>';
			if(is_dir($rootpath.$dn)) { // True
				$dirs .= '<span style="color:green">Exists </span>';
				if(is_writeable($rootpath.$dn)) {
					$dirs .= '<span style="color:green">and is writeable</span>; ';
				} else {
					$dirs .= '<span style="color:red">but is not writeable</span>; ';
				}
			} else {
				$dirs .= '<span style="color:red">Does not exist</span>; ';
			}
		}
		$dirs = trim($dirs, "; ");

		echo $dirs;
	break;

    case "createconfigfile":  
            
        $filename = 'config.txt';                      // This is at root of the file using this script.
        $fd = fopen($filename, "r");                   // opening the file in read mode
        $contents = fread($fd, filesize($filename));   // reading the content of the file
        fclose ($fd);                                   // Closing the file pointer	
	
        $qrepl = array(
            
            '{dbtype}',
            '{server}',
            '{db}',
            '{user}',
            '{password}',
            '{adminuser}',           
            '{portno}',            
            '{idiom_array}',
			'{idiom_flags}',
			'{adminpassword}',
        );
        
        // Handle languages
        $idms = explode(',', $_POST['idiomarray']);  $idioms = "";  
        foreach($idms as $n => $llcd) {
           $idm = explode("|", $llcd);
		   $idioms .= "'".$idm[0]."' => '".$idm[1]."', ";
        }
        $idioms = trim($idioms, "', ");
		
        $idmfs = explode(',', $_POST['idiomflags']);  $idiomfs = "";  
        foreach($idmfs as $n => $llcd) {
           $idmf = explode("|", $llcd);
		   $idiomfs .= "'".$idmf[0]."' => '".$idmf[1]."', ";
        }
        $idiomfs = trim($idiomfs, "', ");
		
        $qwith = array(
           $_POST['dbtype'], 
           $_POST['server'],
           $_POST['db'],
           $_POST['user'],
           $_POST['password'],
           $_POST['adminuser'],
           $_POST['portno'],
           $idioms,
		   $idiomfs,
		   $_POST['adminpassword'],            
        );
        
        $newconfig = str_replace($qrepl, $qwith, $contents);
        
        // Open the file in write mode, if file does not exist then it will be created.
        $configfile = $rootpath."config/config.php";
        $fp = fopen ($configfile, "w"); 
        fwrite ($fp, $newconfig);         		// entering data to the file
        fclose ($fp);                       	// closing the file pointer    
        
        $result = "";
        if(file_exists($configfile)) {
            $result = "File successfully created";
        }
        echo $result;   
    break;

    case "createdatabase":

		// Execute the SQL using the new Config File	
        require $rootpath."config/config.php"; 

        // Create the Database
        if($db['root'] != '') {
			$root = $db['root']; 
			$rootpassword = $db['root_password']; 
        } else {
			$root = $db['user']; 
			$rootpassword = $db['password'];         	
        }

	    try {
	        $dbh = new PDO($db['dbtype'].":host=".$db['server'], $root, $rootpassword);
	        $dbh->exec("CREATE DATABASE `".$db['db']."`;") or die( print_r($dbh->errorInfo(), true) );

	        if($db['root'] != '') {
		        $dbh->exec("CREATE USER '".$db['user']."'@'localhost' IDENTIFIED BY '".$db['password']."';
		            GRANT ALL ON `".$db['db']."`.* TO '".$db['user']."'@'localhost';
		            FLUSH PRIVILEGES;") 
		        or die(print_r($dbh->errorInfo(), true));
	        };

	        echo "<img src='".$rootpath."install/tick.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' />";

	    } catch (PDOException $e) {
	        die("DB ERROR: ". $e->getMessage());
	    }
    break;

    case "createtables":
        
		// Execute the SQL using the new Config File	
        require $rootpath."config/config.php"; 
        dbConnect($db);
        // Now we can work with it
        require_once $rootpath."classes/rb.class.php";
		require_once $rootpath."config/table_setup.php";
		// print_r($clqtables);
		
		// Iterate through the Setup Array to create base tables
		foreach($clqtables as $table => $rows) { 
			if(!R::findOne($table,' 1 LIMIT 1')) {
			
				$table = R::dispense($table);
				foreach($rows as $n => $fld) {
					$table->$fld = 'z';
				}
				$id = R::store($table);
				
			}
		};	

		$ok = R::$f->begin()->select('clq_text')->from('clqstring')->where('id = ?')->put(1)->get('cell');
		if($ok == 'z') {
			echo "<img src='".$rootpath."install/tick.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' />";
		}
	break;		
  
	case "createbasedata":

		// Execute the SQL using the new Config File	
        require $rootpath."config/config.php"; 
        dbConnect($db);
        // Now we can work with it
        require_once $rootpath."classes/rb.class.php";

		// Check to see if the routine has already been run
		// If not, iterate through the initial data array and create data
		$p = R::getCell('select id from clqstring where id = 2');
		if($p == NULL) { 
			
			require $rootpath."install/clqinstall.class.php";
			$dbdata = new sqlImport();
			$result = $dbdata->import($rootpath.'data/clqlite_setup.sql');
			if(file_exists($rootpath.'data/clqlite_setup_'.$lcd.'.sql')) {
				$result .= $dbdata->import($rootpath.'data/clqlite_setup_'.$lcd.'.sql');
			}

		};	

		if(is_numeric($result)) {
			echo "<img src='".$rootpath."install/tick.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' />";
		} else {echo $result;}
    break;   

	case "updatefile":
		
		// Open the file in write mode, if file does not exist then it will be created.
		$filename = $_POST['filename'];	
		$fp = fopen ($filename, "w"); 
		fwrite ($fp,$_POST['newtext']);  
		fclose ($fp);
		echo $filename;			
	break;
	
	case "getfile":
		
		$filename = $_REQUEST['filename']; 					// This is at root of the file using this script.
		$fd = fopen ($filename, "r"); 						// opening the file in read mode
		$text = fread ($fd, filesize($filename)); 			// reading the content of the file
		fclose ($fd);               						// Closing the file pointer									
		echo $text;
	break;

	case "jstr": echo ""; break;

}

function dbConnect($db) {
	
	global $rootpath;
    // Now we can work with it
    require_once $rootpath."classes/rb.class.php";
    // Get Redbean ORM
    switch($db['dbtype']) {
        case "mysql": R::setup('mysql:host='.$db['server'].';dbname='.$db['db'], $db['user'], $db['password']); break; 
        // mysql - 'mysql:host=localhost;dbname=mydatabase','user','password'
        case "pgsql": R::setup('pgsql:host='.$db['server'].';dbname='.$db['db'], $db['user'], $db['password']); break;  
        //postgresql - 'pgsql:host=localhost;dbname=mydatabase','user','password'
        case "sqlite": R::setup('sqlite:../data/clqon_db.txt', $db['user'], $db['password']); break; 
        //sqlite - 'sqlite:/tmp/dbfile.txt','user','password'
    };
}

// End