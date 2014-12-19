--TEST--
MongoDB\Write\Batch: #001 Variety Batch
--SKIPIF--
<?php require "tests/utils/basic-skipif.inc"?>
--FILE--
<?php 
require_once "tests/utils/basic.inc";

$username = "root";
$password = "toor";
$database = "admin";

$parsed = parse_url(MONGODB_STANDALONE_PLAIN_URI);
$dsn = sprintf("mongodb://%s:%s@%s:%d/%s", $username, $password, $parsed["host"], $parsed["port"], $database);
$adminmanager = new MongoDB\Manager($dsn);

$cmd = array(
    "createUser" => "bugs",
    "roles" => [["role" => "readWrite", "db" => DATABASE_NAME]],
);
$command = new MongoDB\Command($cmd);
try {
    $result = $adminmanager->executeCommand('$external', $command);
    echo "User Created\n";
} catch(Exception $e) {
    echo $e->getMessage(), "\n";
}



$username = "bugs";
$password = "password";
$database = '$external';

$dsn = sprintf("mongodb://%s:%s@%s:%d/%s?authMechanism=PLAIN", $username, $password, $parsed["host"], $parsed["port"], $database);
$manager = new MongoDB\Manager($dsn);

$batch = new MongoDB\WriteBatch();
$batch->insert(array("very" => "important"));
try {
    $manager->executeWriteBatch(NS, $batch);
    $query = new MongoDB\Query(array("very" => "important"));
    $cursor = $manager->executeQuery(NS, $query);
    foreach($cursor as $document) {
        var_dump($document["very"]);
    }
    $cmd = new MongoDB\Command(array("drop" => COLLECTION_NAME));
    $result = $manager->executeCommand(DATABASE_NAME, $cmd);
} catch(Exception $e) {
    printf("Caught %s: %s\n", get_class($e), $e->getMessage());
}
$cmd = array(
    "dropUser" => "bugs",
);
$command = new MongoDB\Command($cmd);
try {
    $result = $adminmanager->executeCommand('$external', $command);
    echo "User deleted\n";
} catch(Exception $e) {
    echo $e->getMessage(), "\n";
}
?>
===DONE===
<?php exit(0); ?>
--EXPECT--
User Created
string(9) "important"
User deleted
===DONE===