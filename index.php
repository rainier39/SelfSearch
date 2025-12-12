<?php

// Configuration temporarily here.
$config = array("mhost" => "localhost",
"muser" => "",
"mpass" => "",
"mdb" => "");

$db = mysqli_connect($config["mhost"], $config["muser"], $config["mpass"], $config["mdb"]);

// Will probably want to verify the tables exist etc. TODO

// The page for search results.
if (isset($_GET["q"])) {
  // Measure (roughly) how much time the query took.
  $time = microtime(true);
  // For now just a very simple LIKE with wildcards on either end.
  // Eventually will have more sophisticated searching methods.
  $ids = $db->query("SELECT id FROM `pages` WHERE content LIKE '%" . $db->real_escape_string($_GET["q"]) . "%'");
  $time = microtime(true)-$time;
  echo("<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>SelfSearch</title>
  <meta name='viewport' content='width=device-width,initial-scale=1'>
  <link rel='stylesheet' href='/styles.css'>
</head>
<body>
  <div class='searchbar'>
    <form method='get'>
      <label for='q'>SelfSearch</label>
      <input type='text' name='q' value='" . htmlspecialchars($_GET["q"]) . "'>
      <input type='submit' value='Search'>
    </form>
  </div>
  <div class='resultsheader'>
    Fetched " . $ids->num_rows . " results in " . $time . " seconds.
  </div>
  <div class='results'>");
  // We need to grab each search result's content one at a time to avoid memory issues.
  while ($id = $ids->fetch_assoc()) {
    $results = $db->query("SELECT * FROM `pages` WHERE id='" . $db->real_escape_string($id["id"]) . "'");
    while ($row = $results->fetch_assoc()) {
      if (preg_match("/<title>(.+?)<\/title>/i", $row["content"])) {
        // Kind of a hack here to isolate the title text. Probably isn't efficient.
        echo("<div class='result'><a href='" . htmlspecialchars($row["url"]) . "'><h4>"
         . htmlspecialchars(preg_replace("/.*<title>(.+?)<\/title>.*/is", "$1", $row["content"])) .
        "</h4></a></div>");
      }
    }
  }
  
  echo("  </div>
</body>
</html>
");
}
// The home page (just a searchbar and a title basically).
else {
  echo("<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>SelfSearch</title>
  <meta name='viewport' content='width=device-width,initial-scale=1'>
  <link rel='stylesheet' href='/styles.css'>
</head>
<body>
  <h1>SelfSearch</h1>
  <div class='searchbox'>
    <form method='get'>
      <input type='text' name='q'>
      <input type='submit' value='Search'>
    </form>
  </div>
</body>
</html>
");
}

?>
