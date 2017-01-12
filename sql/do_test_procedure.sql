drop procedure if exists do_test;
delimiter $$
CREATE PROCEDURE do_test (IN n INT, IN l INT, IN k INT, IN machine_id CHAR(36))
BEGIN
      declare _k int unsigned DEFAULT 0;
      declare _n int unsigned DEFAULT 0;
      DECLARE _stmt LONGTEXT DEFAULT 'INSERT INTO major_1 (';

  truncate performance_schema.events_statements_history_long;
  TRUNCATE major_1;
  TRUNCATE major_2;
#   RESET QUERY CACHE;

      WHILE _n < n DO set _n=_n+1;
        set _stmt = CONCAT(_stmt,'minor_',_n,'_id');
        IF _n<n THEN set _stmt = CONCAT(_stmt,','); END IF;
      END WHILE;

      set _stmt = CONCAT(_stmt,') VALUES ');


      while _k < k do set _k=_k+1;
        set _stmt = CONCAT(_stmt,'(');
        set _n=0; WHILE _n < n do set _n=_n+1;
          SET _stmt = CONCAT(_stmt,CEIL(RAND()*l));
          IF _n<n THEN set _stmt = CONCAT(_stmt,','); END IF;
        END WHILE;
        IF _k<k THEN set _stmt = CONCAT(_stmt,'),');
        ELSE set _stmt = CONCAT(_stmt,');'); END IF;
      end while;


#         SELECT _stmt,n,l,k;
        SET @STMT = _stmt;
      PREPARE stmt FROM @stmt;
      EXECUTE stmt;
      DEALLOCATE PREPARE stmt;

  SET FOREIGN_KEY_CHECKS=0;
  INSERT INTO major_2 SELECT * FROM major_1  WHERE 0=0;

  TRUNCATE major_2;
  SET FOREIGN_KEY_CHECKS=1;
  INSERT INTO major_2 SELECT * FROM major_1 WHERE 1=1;

INSERT INTO log SELECT machine_id as machine_id, n as n,l as l,k as k,TRUNCATE(TIMER_WAIT/1000000000000,9) as t, SUBSTR(SQL_TEXT,-3) as message FROM performance_schema.events_statements_history_long WHERE SQL_TEXT like '%execute stmt%' OR SQL_TEXT like '%insert into%';

END $$

delimiter ;