#!/usr/bin/php

<?php require_once("/var/www/dev/mongoTest/functions.php"); ?>

		<?php

			function getTweets($hash_tag) {
			
				//-----MONGO DB Connect------//
			
				//$mongo = new Mongo();
				//$db = $mongo->needtweets;
				//$collection = $db->needs;
				
				//------------MongoDB Connect---------//
				
				//auth call 
				$un = "xxxxxx";
				$pw = "xxxxxx";
				
				//database we have credentials to
				$dtb = "OWS";
				
				$coll = "needs";
				
				//set our collection
				$collection = mongoCollection($un, $pw, $dtb, $coll);
				
				//---------------------------------------------//
				
				

				//-----FIND LAST TWEET ID-----//
				$cursor = $collection->find();
				$idArray = array();
				
				foreach($cursor as $check){
					array_push($idArray,$check["id"]);
				}
				$since_id = max($idArray);

				//--------------------------//
					
				$latest = file_get_contents('http://search.twitter.com/search.json?q='.urlencode($hash_tag).'&since_id='.urlencode($since_id));
							
				$occupyAt = array();
				$occupyHash = array();
				$occupyUser = array();
				
				$tweetCount = json_decode($latest); //how many tweets
				$tweetCount = count($tweetCount->results);
				
				
				for($i=0;$i<=$tweetCount; $i++){
				
					
					$text = json_decode($latest)->results[$i]->text;
					$user = json_decode($latest)->results[$i]->from_user;
					$geo = json_decode($latest)->results[$i]->geo;
					$created = json_decode($latest)->results[$i]->created_at;
					$tweetID = json_decode($latest)->results[$i]->id_str;
					
					//------------FUNCTION PLAYGROUND---------//
					
					$created = new MongoDate(strtotime($created));  //twitter time to mongodate			
					$stripe = removeCommonWords($text);  //remove common words in tweet string
					$occupyLocation = findOccupyLocation($stripe,$user); //find tweet occupy location
					$keywordArray = keywordProcessing($stripe); //making needs searchable keywords array
					
					//----------------------------------------//
					
					

					//-----	Package tweet and DB insert-------//
						
					if ($occupyLocation != NULL && $keywordArray != NULL) { //filtering out unimportant tweets
					


					/*
						$cursor = $collection->find();
						foreach ($cursor as $need) {
						   echo $need["location"] . "\n"; 
						   foreach ($need["need"] as $needarray) {
						   	echo "<h3>".$needarray . "</h3></br>";
						   }
						}
					*/
						
						//----New JSON Object------//
						
						$need = array(
							'created' =>$created,
						    'user'=>$user,
						    'location'=>$occupyLocation,
						    'need'=>$keywordArray,
						    'rawtext'=>$text,
						    'id'=>$tweetID
						);
	
						insertTweet($need,$collection);						
					}
					
					//---------------------------------------//
				}	
				
			}


			function insertTweet($need,$collection){
			
				$safe_insert = true;
				$collection->insert($need,$safe_insert);

			}
			

			function removeCommonWords($eh){

							$commonWords = array('about', 'above', 'above', 'across', 'after', 'afterwards', 'again', 'against', 'all', 'almost', 'alone', 'along', 'already', 'also','although','always',' am ','among', 'amongst', 'amoungst', 'amount', ' an ', 'and', 'another', 'any','anyhow','anyone','anything','anyway', 'anywhere', 'are', 'around', 'back','be','became', 'because','become','becomes', 'becoming', 'been', 'before', 'beforehand', 'behind', 'being', 'below', 'beside', 'besides', 'between', 'beyond', 'bill', 'both', 'bottom','but', ' by ', 'call', 'can', 'cannot', 'cant', ' co ', ' con ', 'could', 'couldnt', 'cry', ' de ', 'describe', 'detail', ' do ', 'done', 'down', ' due ', 'during', 'each', ' eg ', 'eight', 'either', 'eleven','else', 'elsewhere', 'empty', 'enough', 'etc', 'even', 'ever', 'every', 'everyone', 'everything', 'everywhere', 'except', 'few', 'fifteen', 'fifty', 'fill', 'find', 'fire', 'first', 'five', 'for', 'former', 'formerly', 'forty', 'found', 'four', 'from', 'front', 'full', 'further', 'get', 'give', 'go', 'had', 'has', 'hasnt', 'have', 'he', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'hereupon', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'however', 'hundred', ' if ', 'inc', 'indeed', 'interest', 'into', ' is ', 'its', 'itself', 'keep', 'last', 'latter', 'latterly', 'least', 'less', 'made', 'many', 'may', 'me', 'meanwhile', 'might', 'mill', 'mine', 'more', 'moreover', 'most', 'mostly', 'move', 'much', 'must', 'my', 'myself', 'name', 'namely', 'neither', 'never', 'nevertheless', 'next', 'nine', ' no ', 'nobody', 'none', 'noone', 'nor', 'not', 'nothing', 'now', 'nowhere', ' of ', ' off ', 'often', ' on ', 'once', ' one ', 'only', 'onto', ' or ', 'other', 'others', 'otherwise', 'our', 'ours', 'ourselves', 'out', 'over', 'own','part', 'per', 'perhaps', 'please', 'put', 'rather', ' re ', 'same', 'see', 'seem', 'seemed', 'seeming', 'seems', 'serious', 'several', 'she', 'should', 'show', 'side', 'since', 'sincere', 'six', 'sixty', ' so ', 'some', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhere', 'still', 'such', 'system', 'take', ' ten ', 'than', 'that', 'the', 'their', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'therefore', 'therein', 'thereupon', 'these', 'they', 'thickv', 'thin', 'third', 'this', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', ' to ', 'together', 'too', 'top', 'toward', 'towards', 'twelve', 'twenty', 'two', ' un ', 'under', 'until', ' up ', 'upon', 'us', 'very', 'via', ' was', ' we ', 'well', 'were', 'what', 'whatever', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'whereupon', 'wherever', 'whether', 'which', 'while', 'whither', 'who', 'whoever', 'whole', 'whom', 'whose', 'why', 'will', 'with', 'within', 'without', 'would', 'yet', 'you', 'your', 'yours', 'yourself', 'yourselves', ' the ', ' is ', ' on ', ' a ',' I ','Please RT',' We ',' and ',' or ',' of ','requests','please',' A ','RT','with','Please','help','Help',' in ',' at ', 'I ','MT','LOL','lol','need',
'Need',' The ');

				return preg_replace('/\b('.implode('|',$commonWords).')\b/',' ',$eh);
			}

				
				
				
			function findOccupyLocation($stripe,$user){
				
				
				//------- Find Occupy @, #, usernames ---------//
				
				preg_match_all('/[@]Occupy\S*/i', $stripe, $occupyAt); //finding Occupy @ tags
				preg_match_all('/[#]Occupy\S*/i', $stripe, $occupyHash); //finding Occupy # tags
				preg_match('/Occupy\S*/i', $user, $occupyUser); //finding Occupy User

				
				//---------------------------------------------//
				

				$occupyAt_strip = preg_replace("/[#@]/","",$occupyAt[0][0]);
				$occupyHash_strip = preg_replace("/[#@]/","",$occupyHash[0][0]);
				
				
				//--------- Finding Location ---------//
				
				if ($occupyAt_strip != NULL && $occupyAt_strip === $occupyUser[0]){   //if there's an @occupy && it's == to username
					$occupyLocation = $occupyAt[0][0];
				}
				else if ($occupyHash_strip != NULL && $occupyHash_strip === $occupyUser[0]){  //if there's #occupy && it's == to username
					$occupyLocation = $occupyAt[0][0];
				}
				else if ($occupyAt_strip != NULL){
					$occupyLocation = $occupyAt[0][0];
				}
				else if ($occupyUser[0] === "OccupyWallStNYC"){  //username conditions
					$occupyLocation = $occupyUser[0];
				}
				else if ($occupyHash_strip != NULL){
					$occupyLocation = $occupyHash[0][0];
				}
				else if ($occupyUser != NULL){
					$occupyLocation = $occupyUser[0];
				}
				else {
					$occupyLocation = NULL;
				}
				
					           
	           return $occupyLocation;
												
			}
				
				
			function keywordProcessing($stripe){
				
   				$stripe = preg_replace("/http\S*/i"," ",$stripe);  //take out all website links
				$stripe = preg_replace("/[#@]\S*/i"," ",$stripe);  //take out all symbols
				$stripe = preg_replace("/[^a-zA-Z0-9\s]/"," ",$stripe);
				
				//removing all other common words before keywording 
				$stripe = str_replace(array(
				'softheoccupiers','needsoftheoccupiers','asking','Occupy','occupy','99','Any','any','Occupiers','occupiers',
				'really',' use ','lots','How','how','HQ','hq',' amp ',' in ',' or ',' sOf ','Thank','thank',' you ','You', ' s ', ' It ',' If ', ' if ',
				'want','Want','NEED','SUPPORT','need','support',' ty ',' TY ',' it ','LoL',' does ','Always','always'
				), ' ', $stripe);

				//-----keyword array creation-----------------//
				
				$keywords = array();
				$keywords = explode(' ', $stripe);  //make array out of spaces
				$keywordsCondense = array_filter($keywords);  //taking away empty fields
				$keywordsStrip = array_values($keywordsCondense);
				
				//--------------------------------------------//
				

				//-------associative to numerical array-------//
				
				$keywordArray = array();
				
				foreach($keywordsStrip as $key=>$value) {					
					array_push($keywordArray, $value);
				}
				
				//-------------------------------------------//
				
								
				return $keywordArray;
			}


			getTweets('#needsoftheoccupiers'); //start search


			?>
	