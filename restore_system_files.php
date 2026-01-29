<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚑 Restoring Missing System Files...</h1>";

$filesToRestore = [
    'source/function/cache/cache_style.php' => 'https://raw.githubusercontent.com/Discuz-X/DiscuzX/v3.5/upload/source/function/cache/cache_style.php',
    'source/class/memory/memory_driver_file.php' => 'https://raw.githubusercontent.com/Discuz-X/DiscuzX/v3.5/upload/source/class/memory/memory_driver_file.php'
];

$baseDir = __DIR__;

foreach ($filesToRestore as $path => $url) {
    $targetPath = $baseDir . '/' . $path;
    echo "<h3>Target: $path</h3>";

    if (file_exists($targetPath)) {
        echo "File exists. Skipping... (Size: " . filesize($targetPath) . " bytes)<br>";
        continue;
    }

    echo "Downloading from: $url <br>";

    // Use Context for SSL
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    $content = @file_get_contents($url, false, stream_context_create($arrContextOptions));

    if ($content === FALSE || strlen($content) < 100) {
        echo "<b style='color:red'>❌ Download Failed!</b> Check internet or URL.<br>";
        // Fallback: Try curl
        echo "Trying cURL...<br>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        curl_close($ch);
    }

    if ($content && strlen($content) > 100) {
        // Ensure dir exists
        $dir = dirname($targetPath);
        if (!is_dir($dir))
            mkdir($dir, 0777, true);

        if (file_put_contents($targetPath, $content)) {
            echo "<b style='color:green'>✅ Restored Successfully!</b><br>";
        } else {
            echo "<b style='color:red'>❌ Write Failed! Permission Denied.</b><br>";
        }
    } else {
        echo "<b style='color:red'>❌ Failed to download content.</b><br>";
    }
}

echo "<hr>";
echo "<h3>Now please run <a href='force_style_rebuild.php'>Force Style Rebuild</a> again.</h3>";
?>