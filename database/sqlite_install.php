<?php
require __DIR<?php
require __DIR__ . '/../app/bootstrap.php';

$<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(6<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(25<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATET<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_log<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
          image_url VARCHAR(500) NOT NULL DEFAULT '',
          link_url VARCHAR(50<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
          image_url VARCHAR(500) NOT NULL DEFAULT '',
          link_url VARCHAR(500) NOT NULL DEFAULT '',
          ad_type VARCHAR(20) NOT NULL DEFAULT 'image<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
          image_url VARCHAR(500) NOT NULL DEFAULT '',
          link_url VARCHAR(500) NOT NULL DEFAULT '',
          ad_type VARCHAR(20) NOT NULL DEFAULT 'image',
          embed_code TEXT,
          sort_order INTEGER NOT NULL DEFAULT 0,
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
          image_url VARCHAR(500) NOT NULL DEFAULT '',
          link_url VARCHAR(500) NOT NULL DEFAULT '',
          ad_type VARCHAR(20) NOT NULL DEFAULT 'image',
          embed_code TEXT,
          sort_order INTEGER NOT NULL DEFAULT 0,
          enabled TINYINT NOT NULL DEFAULT 1,
          views INTEGER NOT NULL DEFAULT 0<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
          image_url VARCHAR(500) NOT NULL DEFAULT '',
          link_url VARCHAR(500) NOT NULL DEFAULT '',
          ad_type VARCHAR(20) NOT NULL DEFAULT 'image',
          embed_code TEXT,
          sort_order INTEGER NOT NULL DEFAULT 0,
          enabled TINYINT NOT NULL DEFAULT 1,
          views INTEGER NOT NULL DEFAULT 0,
          created_at DATETIME NOT NULL,
          updated_at DATETIME
<?php
require __DIR__ . '/../app/bootstrap.php';

$dbFile = __DIR__ . '/../storage/database.sqlite';

try {
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建表
    $sqlStatements = [
        "CREATE TABLE IF NOT EXISTS admins (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          username VARCHAR(64) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
          name VARCHAR(80) PRIMARY KEY,
          value TEXT,
          updated_at DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS search_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          keyword VARCHAR(200) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          user_agent VARCHAR(255),
          result_count INTEGER NOT NULL DEFAULT 0,
          status VARCHAR(24) NOT NULL DEFAULT 'ok',
          message VARCHAR(255),
          proxy_ip VARCHAR(255),
          search_source VARCHAR(50),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)",
        "CREATE TABLE IF NOT EXISTS page_proxy_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          target_url VARCHAR(800) NOT NULL,
          ip VARCHAR(64) NOT NULL,
          status_code INTEGER NOT NULL DEFAULT 0,
          proxy_ip VARCHAR(255),
          created_at DATETIME NOT NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)",
        "CREATE TABLE IF NOT EXISTS ad_pool (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          pool_key VARCHAR(40) NOT NULL,
          title VARCHAR(120),
          image_url VARCHAR(500) NOT NULL DEFAULT '',
          link_url VARCHAR(500) NOT NULL DEFAULT '',
          ad_type VARCHAR(20) NOT NULL DEFAULT 'image',
          embed_code TEXT,
          sort_order INTEGER NOT NULL DEFAULT 0,
          enabled TINYINT NOT NULL DEFAULT 1,
          views INTEGER NOT NULL DEFAULT 0,
          created_at DATETIME NOT NULL,
          updated_at DATETIME
        )",
        "CREATE INDEX IF NOT EXISTS idx_ad_pool_pool_key ON ad_pool(pool