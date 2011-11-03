<DOCTYPE html>

<head>
<title>Need of the Occupiers</title>
<link rel="stylesheet" type="text/css" href="/js/needs.css">

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script>

//function and http://www.codeforest.net/simple-search-with-php-jquery-and-mysql

$(function() {

    $(".search_button").click(function() {
        // getting the value that user typed
        var searchString    = $("#search_box").val();
        // forming the queryString
        var data            = 'search='+ searchString;

        // if searchString is not empty
        if(searchString) {
            // ajax call
            $.ajax({
                type: "POST",
                url: "do_searchlocal.php",
                data: data,
                beforeSend: function(html) { // this happens before actual call
                    $("#results").html('');
                    $("#searchresults").show();
                    $(".word").html(searchString);
               },
               success: function(html){ // this happens after we get results
                    $("#results").show();
                    $("#results").append(html);
              }
            });
        }
        return false;
    });
});
</script>

</head>
<body>
<div id="container">
<div style="margin:20px auto; text-align: center;">
<b>What do you have to offer?</b>
<form method="post" action="do_searchlocal.php">
    <input type="text" name="search" id="search_box" class='search_box'/>
    <input type="submit" value="Search" class="search_button" /><br />
</form>
</div>
<div>

<div id="searchresults">Search results for <span class="word"></span></div>
<ul id="results" class="update">
</ul>

</div>
</div>

</body>
</html>