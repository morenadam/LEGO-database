<!--
| LEGOPROJEKT SETS			     						|
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
			
			<h2> SETS </h2> 
			
				<?php
					echo "<div id='resultat'>";
					
					//Declare variables
					$servername = "mysql.itn.liu.se";
					$username = "lego";
					$password = "";
					$dbname = "lego";
					
					//Save temporary partID and colorID that were sent from before
					$part = $_GET['partID'];
					$myColorID = $_GET['myColorID'];	   
					$specSearch = $_GET['specSearch'];
					$mySearch = $_GET['mySearch'];
					
					//decides which page that will be displayed
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
						$order = 'sets.SetID';
					}
					
					//Query
					$setresult = mysqli_query($connection, "SELECT DISTINCT sets.Setname, sets.Year, sets.SetID , inventory.Quantity
					FROM sets, inventory
					WHERE (inventory.ItemID = '$part')
					AND inventory.SetID = sets.SetID 
					AND inventory.ColorID = '$myColorID'
					ORDER BY $order 
					LIMIT 12 OFFSET $start");
					
					//encode url
					$mySearch = str_replace(' ', '%20', $mySearch);	
					$myColor = str_replace(' ', '%20', $myColor);
					
					//Buttons for sorting results
					echo"<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?order=sets.SetID&partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$mySearch}'> Order by ID   </a></p> ";
					echo"<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?order=sets.Setname&partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$mySearch}'> Order by Name </a> </p>";
					echo"<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?order=sets.Year&partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$mySearch}'> Order by Year </a> </p> "; 
					echo"<p class='sortera'><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?order=inventory.Quantity&partID={$part}&myColorID={$myColorID}&specSearch={$specSearch}&mySearch={$mySearch}'> Order by Quantity </a> </p> "; 
					
					//Back to parts button
					echo"<h4><a href='http://www.student.itn.liu.se/~ebbni997/legobas/Parts.php?specSearch={$specSearch}&mySearch={$mySearch}'> Back to parts </a></h4>"; 
			
					while ($row2 = mysqli_fetch_array($setresult)) 
					{	
						//declare variables
						$mySetname=$row2['Setname']; 
						$myYear=$row2['Year'];
						$mySetID=$row2['SetID'];
						$myQuantity=$row2['Quantity'];						
						//Determine the file name for the small 80x60 pixels image, with a preference for JPG format.
						$prefix = "http://www.itn.liu.se/~stegu76/img.bricklink.com/";
														
						// Query the database to see which files, if any, are available
						$imagesearch = mysqli_query($connection, "SELECT * 
						FROM images 
						WHERE ItemTypeID='S' 
						AND ItemID='$mySetID'");								
																
						// By design, the query above should return exactly one row.
						$imageinfo = mysqli_fetch_array($imagesearch);								
								
						if($imageinfo['has_jpg']) // Use JPG if it exists
						{ 
							$filename = "S/$mySetID.jpg";
							echo "<div class='setcontent'> <p class='setpartid'> $mySetname </p> <p class='setpartname'> $myYear <br>Quantity of part in set: $myQuantity </p>  <img src=\"$prefix$filename\" alt=\"Set $mySetID\"/> </div> ";  //$row[''] the index here is a field name
						}
								
						else if($imageinfo['has_gif']) // Use GIF if JPG is unavailable	
						{ 
							$filename = "S/$mySetID.gif";
							echo "<div class='setcontent'> <p class='setpartid'> $mySetname </p> <p class='setpartname'> $myYear <br>Quantity of part in set: $myQuantity  </p> <img src=\"$prefix$filename\" alt=\"Set $mySetID\"/></div> ";  //$row[''] the index here is a field name
						} 
								
						else // If neither format is available, insert a placeholder image
						{ 
							$filename = "noimage_small.png";
							echo "<div class='setcontent'> <p class='setpartid'> $mySetname </p> <p class='setpartname'> $myYear <br>Quantity of part in set: $myQuantity  </p><img src=\"$filename\" alt=\"No image\"/> </div> ";  //$row[''] the index here is a field name
						}																
					}				
					
					echo "</div>";
					
					//How many results are being displayed
					$rowcount = mysqli_num_rows($setresult);	
			
					//Using this method to prevent overloading the database
					echo "<div id='pagefooter'>";
					
					if ($rowcount > 11)
					{
						echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?next={$start}&partID={$part}&myColorID={$myColorID}&page={$page}&order={$order}&specSearch={$specSearch}&mySearch={$mySearch}' id='nextpage'> Next Page <br> </a> ";
					
					}
					else //If less than 12 results, next page button is not displayed. 
					{ 
						print "<div id='lastpage'> LAST PAGE OF THESE SETS </div>"; 
					}						
									
					//To prevent user from clicking previous page button on page 1. 
					if ($start > 0)
					{		
						echo "<a href='http://www.student.itn.liu.se/~ebbni997/legobas/Sets.php?previous={$start}&partID={$part}&myColorID={$myColorID}&page={$page}&order={$order}&specSearch={$specSearch}&mySearch={$mySearch}' id='previouspage'> Previous Page  </a> ";					
						
					}						 
					echo "</div>";
					echo "<div id='page'> Page: $page </div>";
				?>
		</div>
		
		<div id="clickMe">
			<a href="#" onclick="Help('popup');"> ? </a>
		</div>
		
		<!-- Popup help for search -->
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
				