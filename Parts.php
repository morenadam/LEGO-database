<!--
| LEGOPROJEKT PARTS     								|
| PROJEKTARBETE I ELEKTRONISK PUBLICERING TNMK30 2016	|
|														|
| GRUPP 11												|
| ADAM MORÉN: adamo472									|
| EMIL MAIORANA: emima732								|
| EBBA NILSSON: ebbni997								|
| SAMUEL SVENSSON: samsv787								|
                                                      -->

<!DOCTYPE html>

<html>
	<head>
		<meta charset ="utf-8">
		<title>AESE LEGO SEARCH</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Dosis">
		<script src="aeselego.js"></script>
	</head>
	
	<body onload="GetNone()">
	
		<div id="wrapper">
			<div class="soka">
			
				<h1><a href="index.html">AESE LEGO SEARCH</a></h1>
			
				<!-- Search Function -->
				<form action="Parts.php" method="GET">
					<input required type="text" id="search" autocomplete="off" name="mySearch" placeholder="Search for Part ID or Part Name..." onfocus="this.placeholder = ''"/> <br>
					<ul>
						<li>
							<input type="radio" id="searchID" name="specSearch" value="parts.PartID" checked="checked">
							<label for="searchID">Search by ID</label>	
							
							<div class="check"><div class="inside"></div></div>
						</li>					  
						<li>
							<input type="radio" id="searchName" name="specSearch" value="parts.Partname">
							<label for="searchName">Search by Name</label>	
							
							<div class="check"><div class="inside"></div></div>
						</li>						 
					</ul>											
				</form>							
			</div>
			
			<h2> PARTS </h2>
			
			<?php
				
				echo "<div id='resultat'>";
				
				//Declare variables
				$servername = "mysql.itn.liu.se";
				$username = "lego";
				$password = "";
				$dbname = "lego";
				
				// User search input
				$myPart = $_GET['mySearch']; 
				$specSearch = $_GET['specSearch']; 
				
				if($specSearch == 'parts.Partname')
				{
					$a = "%";
				}
				else 
				{
					$a = null; 
				}
							
				if(isset($_GET['next']))
				{	
					$page = $_GET['page'] + 1;
				}
				else if (isset($_GET['previous'])) 
				{
					$page = $_GET['page'] - 1;
				}
				else 
				{	
					$page = 1;
				}
				
				//Variable for offset
				$start = ($page - 1) * 12;
				
				//Connect to database
				$connection = mysqli_connect($servername, $username, $password, $dbname); 
	
				//Check connection
				if ($connection->connect_error) 
				{
					die("Connection failed: " . $connection->connect_error);
				} 
				
				if (isset($_GET['order']))
				{
					$order = $_GET['order'];
				}	
				else
				{
					$order = 'parts.PartID';
				}
				
				//Query
				$partresult = mysqli_query($connection, "
				SELECT DISTINCT parts.PartID, parts.Partname, colors.Colorname, inventory.ColorID 
				FROM  parts, colors, inventory
				WHERE $specSearch LIKE '$a$myPart$a' 
				AND parts.PartID = inventory.ItemID
				AND colors.ColorID = inventory.ColorID
				ORDER BY $order 
				LIMIT 12 OFFSET $start");
				
				if($partresult == false)
				{
					print(mysqli_error($connection));
				}	
				
				//How many results are shown
				$rowcount = mysqli_num_rows($partresult);
				
				//encode url
				$myPart = str_replace(' ', '%20', $myPart);	
				$a= str_replace('%', '%25', $a);	
				
				//Buttons for sorting results
				if ($rowcount <= 12 && $rowcount > 0) 
				{	 
			
					echo "<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Parts.php?order=inventory.ItemID&mySearch={$myPart}&specSearch={$specSearch}&a={$a}'> Order by ID  </a>  </p>";
					echo"<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Parts.php?order=parts.Partname&mySearch={$myPart}&specSearch={$specSearch}&a={$a}'> Order by Name </a> </p>"; 
					echo"<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Parts.php?order=colors.Colorname&mySearch={$myPart}&specSearch={$specSearch}&a={$a}'> Order by Color </a> </p> "; 											
				}
				
				
				
				//Print results
				while ($row = mysqli_fetch_array($partresult)) 
				{	
					//Declare variables
					$part =$row['PartID'];
					$mypartname =$row['Partname'];
					$myColor =$row['Colorname'];
					$myColorID = $row['ColorID'];
			
					//Determine the file name for the small 80x60 pixels image, with a preference for JPG format.
					$prefix = "http://www.itn.liu.se/~stegu76/img.bricklink.com/";

					// Query the database to see which files, if any, are available
					$imagesearch = mysqli_query($connection, "SELECT * 
					FROM images 
					WHERE ItemTypeID='P' 
					AND ItemID='$part' 
					AND ColorID=$myColorID");
					
					// By design, the query above should return exactly one row.
					$imageinfo = mysqli_fetch_array($imagesearch);
					
					if($imageinfo['has_jpg']) // Use JPG if it exists
					{ 	
						$filename = "P/$myColorID/$part.jpg";
							   
						echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$myPart}'>
						<div class='content'> <p class='partid'> Part ID: $part  </p> <br> <p class='partname'> $mypartname </p> <br> <p class='colorname'> $myColor </p> <br> 
						<img src=\"$prefix$filename\" alt=\"Part $part\"/></div> 
						</a>"; 					
					
					} 			
					else if($imageinfo['has_gif']) // Use GIF if JPG is unavailable
					{    
						$filename = "P/$myColorID/$part.gif";
							   
						echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$myPart}'>
						<div class='content'> <p class='partid'> Part ID: $part  </p> <br> <p class='partname'> $mypartname </p> <br> <p class='colorname'> $myColor </p> <br> 
						<img src=\"$prefix$filename\" alt=\"Part $part\"/> </div> 
						</a> "; 
												
					} 				
					else 
					{ // If neither format is available, insert a placeholder image
						$filename = "noimage_small.png";
															
						echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$myPart}'>
						<div class='content'> <p class='partid'> Part ID: $part  </p> <br> <p class='partname'> $mypartname </p> <br> <p class='colorname'> $myColor </p> <br> 
						<img src=\"$filename\" alt=\"Part $part\"/> </div> 
						</a>"; 	
												
					}
					
				}	
				
				echo "</div>";

				echo "<div id='pagefooter'>";
		
				if ($rowcount > 11)
				{
					echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Parts.php?mySearch={$myPart}&specSearch={$specSearch}&a={$a}&page={$page}&order={$order}&next' id='nextpage'> Next Page  <br> </a>  ";
					
				}
				else if ($rowcount == NULL && $page == 1) 
				{ 
					print "<div id='noresults'> No results found! </div>";
				}
				else
				{
					print "<div id='lastpage'>LAST PAGE OF THESE PARTS</div>";
				}
				
				if ($start > 0)
				{	
					echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Parts.php?mySearch={$myPart}&specSearch={$specSearch}&a={$a}&page={$page}&order={$order}&previous' id='previouspage'> Previous Page  </a> ";			
				
				}			
			
				if ($rowcount <= 12 && $rowcount > 0)
				{
					echo "<div id='page'> Page: $page </div>"; 
				}
				
				echo"</div>";
				
				
				
			?>						
				
		</div>  
		
		<!-- Popup help for search -->
		<div id="clickMe">
				<a href="#" onclick="Help('popup');"> ? </a>
		</div>
		
		<div id="popup">
			<p id="hjalprubrik"> How to search: </p>
			<p id="hjalptext"> 
				<b>Part ID:</b> Type the correct ID of your part. For example: "3004", "2340" or "3005" <br>
				<b>Part Name:</b> Type the name of the part you want to find. For example "Brick 2 x 2", "Star Wars" or "Police" <br>
				Try it yourself!
			</p>
		</div>	
		
		<canvas id="canvas"></canvas>
	
	</body>
</html>