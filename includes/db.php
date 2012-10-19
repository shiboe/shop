<?php

/**
 * class implements a simplified database interaction using PDO
 * 
 * @author Stephen Cave
 * @link sccave@gmail.com
 * @copyright 2011 Stephen Cave
 * 
 * @version 1.0
 */
class db
{
    protected static $name = "retrogra_shopZon";
    protected static $host = DB_HOST;
    
    protected static $user = DB_USER;
    protected static $pass = DB_PASSWORD;
    
    /**
     * variable linking to the PDO connection
     * 
     * Should the db class not have the desired functions to operate
     * on the PDO connection, one can access it directly through $conn. 
     * 
     * @var PDO
     */
    public static $conn;
    
    /**
     * variable linking to the last PDO statement
     * 
     * Should the db class not have the desired functions to operate on the
     * current PDO statement, one can access it directly through $statement. 
     * 
     * @var PDOstatement
     */
    public static $statement;
    
    /**
     *
     * make a parameterized query to the database
     * 
     * @param string $q_string "SELECT * FROM table WHERE var = :var"
     * @param array $param_array array(":var" => "value")
     */
    public static function query($q_string, $param_array = null)
    {
        db::connect();
        
        db::$statement = db::$conn->prepare($q_string);
        
        db::$statement->execute($param_array);
        
        if(db::$statement->errorCode() != "00000")
        {
            $error = db::$statement->errorInfo();
            error_log("db error : " . $error[2]);
            throw new Exception($error[2]);
        }
        
        return db::$statement;
    }
    
    /**
     * fetch associative array from the query
     * 
     * returns an associative array off the PDOstatement passed via param,
     * or if no statement is passed, the last statement is used.
     * 
     * @param PDOstatement $PDOstatement
     * @return PDOstatement 
     */
    public static function fetch_assoc( $PDOstatement = null )
    {
        if($PDOstatement)
        {
            return $PDOstatement->fetch(PDO::FETCH_ASSOC);
        }
        
        return db::$statement->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * get the number of rows from the query
     * 
     * returns the humberof rows of the PDOstatement passed via param,
     * or if no statement is passed, the last statement is used.
     * 
     * @param PDOstatement $PDOstatement
     * @return PDOstatement 
     */
    public static function num_rows( $PDOstatement = null )
    {
        if($PDOstatement)
        {
            return $PDOstatement->rowCount();
        }
        
        return db::$statement->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public static function transaction_begin()
    {
        db::connect();
        
        db::$conn->beginTransaction();
    }
    
    /**
     * End transaction, commit all queries
     */
    public static function transaction_complete()
    {
        db::$conn->commit();
    }
    
    /**
     * converts a php timestamp to mysql timestamp, or makes a mysqltimestamp
     * of the current time if no timestamp is passed
     * 
     * @param time() $php_timestamp
     * @return datetime 
     */
    public static function to_mysql_timestamp($php_timestamp = null)
    {
        if($php_timestamp)
        {
            return date( 'Y-m-d H:i:s', $php_timestamp );
        }
        
        return date( 'Y-m-d H:i:s', time() );
    }
    
    /**
     * Converts a SQL datetime to a php timestamp
     * 
     * @param datetime $sql_datetime
     * @return time() 
     */
    public static function to_php_timestamp($sql_datetime)
    {
        return strtotime($sql_datetime);
    }
    
    /**
     * Call to initiate PDO database cannection
     */
    protected static function connect()
    {
        if( ! db::$conn )
        {
            db::$conn = new PDO(  db::pdo_dsn(), db::$user, db::$pass );
        }
    }
    
    /**
     * Format the connection dsn string for a PDO connection
     * 
     * @return string 
     */
    protected static function pdo_dsn()
    {
        return "mysql:host=".db::$host.";dbname=".db::$name;
    }
}
