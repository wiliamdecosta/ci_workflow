-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2021 at 03:18 PM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 5.6.39

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ci_mysql`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `p_submit_engine_next_job` (IN `i_curr_t_order_control_id` INT, IN `i_curr_ref_t_order_control_id` INT, IN `i_p_w_doc_type_id` INT, IN `i_curr_job_wf_id` INT, IN `i_next_job_wf_id` INT, IN `s_curr_user_submitter` VARCHAR(25), IN `i_order_id` INT, IN `s_order_no` VARCHAR(10), IN `s_message` VARCHAR(255), OUT `o_ret_id` INT, OUT `o_ret_message` VARCHAR(1000))  NO SQL
proc_label:BEGIN 
  DECLARE ln_user_id_donor int(10);
  DECLARE ls_function_name_after_submit varchar(255);
  DECLARE ls_ret_val_fn varchar(1000);
  DECLARE ln_order_id int(10);
  
  
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
    GET DIAGNOSTICS CONDITION 1
	@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT;

     SET o_ret_id = -999;
  	 SET o_ret_message = concat('error submit - ', @p2);
     
     ROLLBACK;
     SET autocommit = 1;
    END;
  
  SET autocommit = 0;
  START TRANSACTION;
    
  SELECT user_id into ln_user_id_donor
  FROM users WHERE user_name = s_curr_user_submitter;
  
  IF ln_user_id_donor is null THEN
    SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - user id donnor tidak dikenal';
  	LEAVE proc_label;
  END IF;
  
  SELECT t_order_id into ln_order_id
  FROM t_order
  WHERE order_no = s_order_no;
  
  if ln_order_id is null THEN
  	SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - nomor order tidak dikenal';
  	LEAVE proc_label;
  end if;
  
  /**
  * Pengecekkan i_next_job_wf_id, JIKA :
  * 1). i_next_job_wf_id = 0 atau is null, maka 
  *     INBOX diupdate menjadi FINISH tanpa penambahan new row
  * 2). i_next_job_wf_id != 0 atau is not null, maka
  *     INBOX diupdate menjadi OUTBOX dan tambahkan new row
  *     beserta ref_control_id nya
  */
  
  begin
  	
      if i_next_job_wf_id is null or i_next_job_wf_id = 0 THEN
			UPDATE t_order_control_wf
            SET user_id_submitter = ln_user_id_donor,
            submit_date = now(),
            profile_type = 'FINISH'
            WHERE t_order_control_id = i_curr_t_order_control_id;
            
            UPDATE t_order
            SET p_order_status_id = 3
            WHERE t_order_id = ln_order_id;
			
           
      else
			
            UPDATE t_order_control_wf
            SET user_id_submitter = ln_user_id_donor,
            submit_date = now(),
            profile_type = 'OUTBOX'
            WHERE t_order_control_id = i_curr_t_order_control_id;
            
            INSERT INTO t_order_control_wf
            (ref_order_control_id, p_w_doc_type_id, p_w_job_wf_id, user_id_donor, donor_date, message, order_id, order_no, profile_type)
            VALUES(i_curr_t_order_control_id, i_p_w_doc_type_id, i_next_job_wf_id, ln_user_id_donor, now(), s_message, ln_order_id, s_order_no, 'INBOX');
			
            
      end if;
  end;    
  
  
  SELECT f_after_submit into ls_function_name_after_submit
  FROM p_job_wf WHERE job_wf_id = i_curr_job_wf_id;
     
  
  if ls_function_name_after_submit is not null THEN
  
  	   set @ls_ret_val_fn = '';
       set @sql = CONCAT('SET @ls_ret_val_fn = ',ls_function_name_after_submit,'(',i_curr_t_order_control_id,',''',s_order_no,''',''',s_curr_user_submitter,''')'); 
    
        PREPARE str_sql FROM @sql;
        EXECUTE str_sql;
        DEALLOCATE PREPARE str_sql;

        if @ls_ret_val_fn != '' and @ls_ret_val_fn != 'sukses' then 
        	SET o_ret_id = -999;
            SET o_ret_message = concat('error submit - ', @ls_ret_val_fn);
            ROLLBACK;
            SET autocommit = 1;
            LEAVE proc_label;
        end if;
  end if;
  
  COMMIT;
  SET o_ret_id = 0;
  SET o_ret_message = 'sukses';
  SET autocommit = 1;
  	

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_submit_engine_reject_job` (IN `i_curr_t_order_control_id` INT, IN `i_curr_ref_t_order_control_id` INT, IN `i_p_w_doc_type_id` INT, IN `i_curr_job_wf_id` INT, IN `i_next_job_wf_id` INT, IN `s_curr_user_submitter` VARCHAR(25), IN `i_order_id` INT, IN `s_order_no` VARCHAR(10), IN `s_message` VARCHAR(255), OUT `o_ret_id` INT, OUT `o_ret_message` VARCHAR(1000))  NO SQL
proc_label:BEGIN 
  DECLARE ln_user_id_donor int(10);
  DECLARE ls_function_name_after_reject varchar(255);
  DECLARE ls_ret_val_fn varchar(1000);
  DECLARE ln_order_id int(10);
  
  
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
    GET DIAGNOSTICS CONDITION 1
	@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT;

     SET o_ret_id = -999;
  	 SET o_ret_message = concat('error submit - ', @p2);
     
     ROLLBACK;
     SET autocommit = 1;
    END;
  
  SET autocommit = 0;
  START TRANSACTION;
    
  SELECT user_id into ln_user_id_donor
  FROM users WHERE user_name = s_curr_user_submitter;
  
  IF ln_user_id_donor is null THEN
    SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - user id submitter tidak dikenal';
  	LEAVE proc_label;
  END IF;
  
  SELECT t_order_id into ln_order_id
  FROM t_order
  WHERE order_no = s_order_no;
  
  if ln_order_id is null THEN
  	SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - nomor order tidak dikenal';
  	LEAVE proc_label;
  end if;
  
  /**
  * Proses Reject tidak memerlukan add new row, hanya mengubah INBOX menjadi REJECT
  * 
  */
  
  begin
  	
    UPDATE t_order_control_wf
    SET user_id_submitter = ln_user_id_donor,
    submit_date = now(),
    profile_type = 'REJECT',
    message = concat('** Rejected by : ',s_curr_user_submitter, ' ** : ', s_message, COALESCE(concat(' | ', message), ''))
    WHERE t_order_control_id = i_curr_t_order_control_id;

    UPDATE t_order
    SET p_order_status_id = 4
    WHERE t_order_id = ln_order_id;
    
  end;    
  
  
  SELECT f_after_reject into ls_function_name_after_reject
  FROM p_job_wf WHERE job_wf_id = i_curr_job_wf_id;
  
  
  if ls_function_name_after_reject is not null THEN
  
  	   set @ls_ret_val_fn = '';
       set @sql = CONCAT('SET @ls_ret_val_fn = ',ls_function_name_after_reject,'(',i_curr_t_order_control_id,',''',s_order_no,''',''',s_curr_user_submitter,''')'); 
    
        PREPARE str_sql FROM @sql;
        EXECUTE str_sql;
        DEALLOCATE PREPARE str_sql;

        if @ls_ret_val_fn != '' and @ls_ret_val_fn != 'sukses' then 
        	SET o_ret_id = -999;
            SET o_ret_message = concat('error submit - ', @ls_ret_val_fn);
            ROLLBACK;
            SET autocommit = 1;
            LEAVE proc_label;
        end if;
  end if;
  
  COMMIT;
  SET o_ret_id = 0;
  SET o_ret_message = 'sukses';
  SET autocommit = 1;
  	

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_submit_engine_sendback_job` (IN `i_curr_t_order_control_id` INT, IN `i_curr_ref_t_order_control_id` INT, IN `i_p_w_doc_type_id` INT, IN `i_curr_job_wf_id` INT, IN `i_prev_job_wf_id` INT, IN `s_curr_user_submitter` VARCHAR(25), IN `i_order_id` INT, IN `s_order_no` VARCHAR(10), IN `s_message` VARCHAR(255), OUT `o_ret_id` INT, OUT `o_ret_message` VARCHAR(1000))  NO SQL
proc_label:BEGIN 
  DECLARE ln_user_id_donor int(10);
  DECLARE ls_function_name_after_sendback varchar(255);
  DECLARE ls_ret_val_fn varchar(1000);
  DECLARE ln_order_id int(10);
  
  
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
    GET DIAGNOSTICS CONDITION 1
	@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT;

     SET o_ret_id = -999;
  	 SET o_ret_message = concat('error submit - ', @p2);
     
     ROLLBACK;
     SET autocommit = 1;
    END;
  
  SET autocommit = 0;
  START TRANSACTION;
  
  /*  
  IF i_curr_ref_t_order_control_id is null or i_curr_ref_t_order_control_id = 0 THEN
    SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - ref_t_order_control_id tidak boleh kosong';
  	LEAVE proc_label;
  END IF;
  
  IF i_prev_job_wf_id is null THEN
    SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - prev_job_wf_id tidak boleh kosong';
  	LEAVE proc_label;
  END IF;
  */
  
  SELECT user_id into ln_user_id_donor
  FROM users WHERE user_name = s_curr_user_submitter;
  
  IF ln_user_id_donor is null THEN
    SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - user id submitter tidak dikenal';
  	LEAVE proc_label;
  END IF;
  
    
  SELECT t_order_id into ln_order_id
  FROM t_order
  WHERE order_no = s_order_no;
  
  if ln_order_id is null THEN
  	SET o_ret_id = -999;
  	SET o_ret_message = 'error submit - nomor order tidak dikenal';
  	LEAVE proc_label;
  end if;
  
  /**
  * Proses Send Back :
  * Update Id Donnor, Update Message, Update profile_type Menjadi INBOX 
  * Delete baris data yang bersangkutan
  */
  
  begin
  	if i_prev_job_wf_id is not null and i_prev_job_wf_id != 0 then
		
		UPDATE t_order_control_wf
		SET user_id_donor = ln_user_id_donor,
		donor_date = now(),
		profile_type = 'INBOX',
		message = concat('** Send back job from ',s_curr_user_submitter,' ** : ',s_message, COALESCE(concat(' | ',message), ''))
		WHERE t_order_control_id = i_curr_ref_t_order_control_id;

		
		DELETE FROM t_order_control_wf
		WHERE t_order_control_id = i_curr_t_order_control_id;
        
    ELSE
    	/* back to state before f_first_submit */
        DELETE FROM t_order_control_wf
		WHERE t_order_control_id = i_curr_t_order_control_id;
        
        UPDATE t_order
        SET p_order_status_id = 1
        WHERE t_order_id = ln_order_id;
    end if;
  end;    
  
  
  SELECT f_after_send_back into ls_function_name_after_sendback
  FROM p_job_wf WHERE job_wf_id = i_curr_job_wf_id;
  
  
  if ls_function_name_after_sendback is not null THEN
  
  	   set @ls_ret_val_fn = '';
       set @sql = CONCAT('SET @ls_ret_val_fn = ',ls_function_name_after_sendback,'(',i_curr_t_order_control_id,',''',s_order_no,''',''',s_curr_user_submitter,''')'); 
    
        PREPARE str_sql FROM @sql;
        EXECUTE str_sql;
        DEALLOCATE PREPARE str_sql;

        if @ls_ret_val_fn != '' and @ls_ret_val_fn != 'sukses' then 
        	SET o_ret_id = -999;
            SET o_ret_message = concat('error submit - ', @ls_ret_val_fn);
            ROLLBACK;
            SET autocommit = 1;
            LEAVE proc_label;
        end if;
  end if;
  
  COMMIT;
  SET o_ret_id = 0;
  SET o_ret_message = 'sukses';
  SET autocommit = 1;
  	

END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `f_after_pengesahan_cuti` (`i_curr_t_order_control_id` INT, `s_order_no` VARCHAR(10), `s_user_login` VARCHAR(25)) RETURNS VARCHAR(1000) CHARSET latin1 NO SQL
BEGIN

   DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
    GET DIAGNOSTICS CONDITION 1
	@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT;

     return concat('error in function - ', @p2);
    END;
    
   UPDATE t_registrasi_cuti
       SET is_accepted = 'Y',
       updated_date = now(),
       updated_by = s_user_login
	WHERE order_no = s_order_no;
    
   return 'sukses';
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_after_ver_cuti` (`i_curr_t_order_control_id` INT, `s_order_no` VARCHAR(10), `s_user_login` VARCHAR(25)) RETURNS VARCHAR(1000) CHARSET latin1 NO SQL
BEGIN

   DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
    GET DIAGNOSTICS CONDITION 1
	@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT;

     return concat('error in function - ', @p2);
    END;
   
   UPDATE t_registrasi_cuti
       SET is_verified = 'Y',
       updated_date = now(),
       updated_by = s_user_login
	WHERE order_no = s_order_no;
    
   return 'sukses';
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_first_submit` (`i_doc_type_id` INT, `s_order_no` VARCHAR(10), `s_user_name` VARCHAR(25)) RETURNS VARCHAR(255) CHARSET latin1 NO SQL
BEGIN 
  DECLARE ln_p_document_type_id int(10);
  DECLARE ln_t_order_id int(10);
  DECLARE ln_user_id_donor int(10);
  DECLARE ln_1st_job_wf_id int(10);
  
  SELECT p_document_type_id into ln_p_document_type_id
  FROM p_document_type WHERE p_document_type_id = i_doc_type_id;
  
  if(ln_p_document_type_id is null) then
  	return 'Error - ID dokumen tidak valid';
  end if;
  
  SELECT t_order_id into ln_t_order_id
  FROM t_order WHERE order_no = s_order_no;
  
  if(ln_t_order_id is null) then
  	return 'Error - No Order tidak valid';
  end if;
  
  SELECT user_id into ln_user_id_donor
  FROM users WHERE user_name = s_user_name;
  
  if(ln_user_id_donor is null) THEN
  	return 'Error - Username tidak valid';
  end if;
  
  SELECT prev_job_wf_id into ln_1st_job_wf_id
  FROM p_workflow
  WHERE p_workflow_id IN ( SELECT min(p_workflow_id) FROM p_workflow                                     WHERE p_document_type_id = i_doc_type_id);
  
  
  
  insert into t_order_control_wf(p_w_doc_type_id, p_w_job_wf_id, user_id_donor, donor_date, order_id, order_no, profile_type)
values(ln_p_document_type_id, ln_1st_job_wf_id, ln_user_id_donor, now(), ln_t_order_id, s_order_no, 'INBOX');

UPDATE t_order
SET p_order_status_id = 2
WHERE t_order_id = ln_t_order_id;

  RETURN 'sukses';
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_order_no` () RETURNS VARCHAR(10) CHARSET latin1 BEGIN 
  DECLARE s_order_no char(10);

  select lpad((select coalesce(max(t_order_id),0) from t_order) + 1, 10, '0') into s_order_no;

  INSERT INTO t_order(order_no, order_date, p_order_status_id) VALUES(s_order_no, now(), 1);  

  RETURN s_order_no;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `icons`
--

CREATE TABLE `icons` (
  `icon_id` int(11) NOT NULL,
  `icon_code` varchar(255) NOT NULL,
  `icon_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `icons`
--

INSERT INTO `icons` (`icon_id`, `icon_code`, `icon_description`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 'fa fa-adjust', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 'fa fa-adn', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(3, 'fa fa-align-center', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(4, 'fa fa-align-justify', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(5, 'fa fa-align-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(6, 'fa fa-align-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(7, 'fa fa-ambulance', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(8, 'fa fa-anchor', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(9, 'fa fa-android', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(10, 'fa fa-angellist', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(11, 'fa fa-angle-double-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(12, 'fa fa-angle-double-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(13, 'fa fa-angle-double-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(14, 'fa fa-angle-double-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(15, 'fa fa-angle-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(16, 'fa fa-angle-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(17, 'fa fa-angle-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(18, 'fa fa-angle-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(19, 'fa fa-apple', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(20, 'fa fa-archive', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(21, 'fa fa-area-chart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(22, 'fa fa-arrow-circle-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(23, 'fa fa-arrow-circle-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(24, 'fa fa-arrow-circle-o-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(25, 'fa fa-arrow-circle-o-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(26, 'fa fa-arrow-circle-o-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(27, 'fa fa-arrow-circle-o-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(28, 'fa fa-arrow-circle-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(29, 'fa fa-arrow-circle-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(30, 'fa fa-arrow-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(31, 'fa fa-arrow-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(32, 'fa fa-arrow-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(33, 'fa fa-arrow-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(34, 'fa fa-arrows', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(35, 'fa fa-arrows-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(36, 'fa fa-arrows-h', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(37, 'fa fa-arrows-v', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(38, 'fa fa-asterisk', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(39, 'fa fa-at', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(40, 'fa fa-automobile', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(41, 'fa fa-backward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(42, 'fa fa-ban', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(43, 'fa fa-bank', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(44, 'fa fa-bar-chart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(45, 'fa fa-bar-chart-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(46, 'fa fa-barcode', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(47, 'fa fa-bars', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(48, 'fa fa-bed', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(49, 'fa fa-beer', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(50, 'fa fa-behance', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(51, 'fa fa-behance-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(52, 'fa fa-bell', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(53, 'fa fa-bell-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(54, 'fa fa-bell-slash', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(55, 'fa fa-bell-slash-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(56, 'fa fa-bicycle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(57, 'fa fa-binoculars', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(58, 'fa fa-birthday-cake', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(59, 'fa fa-bitbucket', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(60, 'fa fa-bitbucket-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(61, 'fa fa-bitcoin', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(62, 'fa fa-bold', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(63, 'fa fa-bolt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(64, 'fa fa-bomb', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(65, 'fa fa-book', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(66, 'fa fa-bookmark', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(67, 'fa fa-bookmark-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(68, 'fa fa-briefcase', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(69, 'fa fa-btc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(70, 'fa fa-bug', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(71, 'fa fa-building', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(72, 'fa fa-building-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(73, 'fa fa-bullhorn', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(74, 'fa fa-bullseye', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(75, 'fa fa-bus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(76, 'fa fa-buysellads', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(77, 'fa fa-cab', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(78, 'fa fa-calculator', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(79, 'fa fa-calendar', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(80, 'fa fa-calendar-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(81, 'fa fa-camera', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(82, 'fa fa-camera-retro', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(83, 'fa fa-car', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(84, 'fa fa-caret-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(85, 'fa fa-caret-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(86, 'fa fa-caret-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(87, 'fa fa-caret-square-o-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(88, 'fa fa-caret-square-o-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(89, 'fa fa-caret-square-o-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(90, 'fa fa-caret-square-o-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(91, 'fa fa-caret-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(92, 'fa fa-cart-arrow-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(93, 'fa fa-cart-plus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(94, 'fa fa-cc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(95, 'fa fa-cc-amex', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(96, 'fa fa-cc-discover', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(97, 'fa fa-cc-mastercard', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(98, 'fa fa-cc-paypal', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(99, 'fa fa-cc-stripe', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(100, 'fa fa-cc-visa', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(101, 'fa fa-certificate', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(102, 'fa fa-chain', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(103, 'fa fa-chain-broken', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(104, 'fa fa-check', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(105, 'fa fa-check-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(106, 'fa fa-check-circle-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(107, 'fa fa-check-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(108, 'fa fa-check-square-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(109, 'fa fa-chevron-circle-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(110, 'fa fa-chevron-circle-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(111, 'fa fa-chevron-circle-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(112, 'fa fa-chevron-circle-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(113, 'fa fa-chevron-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(114, 'fa fa-chevron-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(115, 'fa fa-chevron-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(116, 'fa fa-chevron-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(117, 'fa fa-child', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(118, 'fa fa-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(119, 'fa fa-circle-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(120, 'fa fa-circle-o-notch', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(121, 'fa fa-circle-thin', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(122, 'fa fa-clipboard', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(123, 'fa fa-clock-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(124, 'fa fa-close', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(125, 'fa fa-cloud', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(126, 'fa fa-cloud-download', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(127, 'fa fa-cloud-upload', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(128, 'fa fa-cny', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(129, 'fa fa-code', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(130, 'fa fa-code-fork', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(131, 'fa fa-codepen', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(132, 'fa fa-coffee', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(133, 'fa fa-cog', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(134, 'fa fa-cogs', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(135, 'fa fa-columns', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(136, 'fa fa-comment', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(137, 'fa fa-comment-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(138, 'fa fa-comments', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(139, 'fa fa-comments-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(140, 'fa fa-compass', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(141, 'fa fa-compress', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(142, 'fa fa-connectdevelop', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(143, 'fa fa-copy', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(144, 'fa fa-copyright', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(145, 'fa fa-credit-card', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(146, 'fa fa-crop', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(147, 'fa fa-crosshairs', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(148, 'fa fa-css3', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(149, 'fa fa-cube', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(150, 'fa fa-cubes', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(151, 'fa fa-cut', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(152, 'fa fa-cutlery', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(153, 'fa fa-dashboard', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(154, 'fa fa-dashcube', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(155, 'fa fa-database', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(156, 'fa fa-dedent', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(157, 'fa fa-delicious', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(158, 'fa fa-desktop', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(159, 'fa fa-deviantart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(160, 'fa fa-diamond', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(161, 'fa fa-digg', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(162, 'fa fa-dollar', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(163, 'fa fa-dot-circle-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(164, 'fa fa-download', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(165, 'fa fa-dribbble', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(166, 'fa fa-dropbox', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(167, 'fa fa-drupal', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(168, 'fa fa-edit', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(169, 'fa fa-eject', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(170, 'fa fa-ellipsis-h', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(171, 'fa fa-ellipsis-v', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(172, 'fa fa-empire', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(173, 'fa fa-envelope', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(174, 'fa fa-envelope-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(175, 'fa fa-envelope-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(176, 'fa fa-eraser', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(177, 'fa fa-eur', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(178, 'fa fa-euro', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(179, 'fa fa-exchange', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(180, 'fa fa-exclamation', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(181, 'fa fa-exclamation-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(182, 'fa fa-exclamation-triangle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(183, 'fa fa-expand', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(184, 'fa fa-external-link', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(185, 'fa fa-external-link-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(186, 'fa fa-eye', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(187, 'fa fa-eye-slash', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(188, 'fa fa-eyedropper', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(189, 'fa fa-facebook', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(190, 'fa fa-facebook-f', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(191, 'fa fa-facebook-official', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(192, 'fa fa-facebook-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(193, 'fa fa-fast-backward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(194, 'fa fa-fast-forward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(195, 'fa fa-fax', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(196, 'fa fa-female', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(197, 'fa fa-fighter-jet', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(198, 'fa fa-file', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(199, 'fa fa-file-archive-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(200, 'fa fa-file-audio-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(201, 'fa fa-file-code-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(202, 'fa fa-file-excel-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(203, 'fa fa-file-image-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(204, 'fa fa-file-movie-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(205, 'fa fa-file-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(206, 'fa fa-file-pdf-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(207, 'fa fa-file-photo-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(208, 'fa fa-file-picture-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(209, 'fa fa-file-powerpoint-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(210, 'fa fa-file-sound-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(211, 'fa fa-file-text', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(212, 'fa fa-file-text-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(213, 'fa fa-file-video-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(214, 'fa fa-file-word-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(215, 'fa fa-file-zip-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(216, 'fa fa-files-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(217, 'fa fa-film', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(218, 'fa fa-filter', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(219, 'fa fa-fire', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(220, 'fa fa-fire-extinguisher', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(221, 'fa fa-flag', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(222, 'fa fa-flag-checkered', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(223, 'fa fa-flag-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(224, 'fa fa-flash', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(225, 'fa fa-flask', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(226, 'fa fa-flickr', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(227, 'fa fa-floppy-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(228, 'fa fa-folder', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(229, 'fa fa-folder-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(230, 'fa fa-folder-open', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(231, 'fa fa-folder-open-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(232, 'fa fa-font', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(233, 'fa fa-forumbee', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(234, 'fa fa-forward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(235, 'fa fa-foursquare', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(236, 'fa fa-frown-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(237, 'fa fa-futbol-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(238, 'fa fa-gamepad', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(239, 'fa fa-gavel', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(240, 'fa fa-gbp', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(241, 'fa fa-ge', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(242, 'fa fa-gear', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(243, 'fa fa-gears', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(244, 'fa fa-genderless', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(245, 'fa fa-gift', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(246, 'fa fa-git', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(247, 'fa fa-git-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(248, 'fa fa-github', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(249, 'fa fa-github-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(250, 'fa fa-github-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(251, 'fa fa-gittip', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(252, 'fa fa-glass', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(253, 'fa fa-globe', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(254, 'fa fa-google', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(255, 'fa fa-google-plus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(256, 'fa fa-google-plus-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(257, 'fa fa-google-wallet', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(258, 'fa fa-graduation-cap', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(259, 'fa fa-gratipay', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(260, 'fa fa-group', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(261, 'fa fa-h-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(262, 'fa fa-hacker-news', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(263, 'fa fa-hand-o-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(264, 'fa fa-hand-o-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(265, 'fa fa-hand-o-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(266, 'fa fa-hand-o-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(267, 'fa fa-hdd-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(268, 'fa fa-header', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(269, 'fa fa-headphones', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(270, 'fa fa-heart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(271, 'fa fa-heart-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(272, 'fa fa-heartbeat', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(273, 'fa fa-history', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(274, 'fa fa-home', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(275, 'fa fa-hospital-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(276, 'fa fa-hotel', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(277, 'fa fa-html5', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(278, 'fa fa-ils', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(279, 'fa fa-image', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(280, 'fa fa-inbox', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(281, 'fa fa-indent', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(282, 'fa fa-info', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(283, 'fa fa-info-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(284, 'fa fa-inr', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(285, 'fa fa-instagram', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(286, 'fa fa-institution', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(287, 'fa fa-ioxhost', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(288, 'fa fa-italic', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(289, 'fa fa-joomla', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(290, 'fa fa-jpy', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(291, 'fa fa-jsfiddle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(292, 'fa fa-key', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(293, 'fa fa-keyboard-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(294, 'fa fa-krw', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(295, 'fa fa-language', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(296, 'fa fa-laptop', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(297, 'fa fa-lastfm', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(298, 'fa fa-lastfm-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(299, 'fa fa-leaf', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(300, 'fa fa-leanpub', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(301, 'fa fa-legal', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(302, 'fa fa-lemon-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(303, 'fa fa-level-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(304, 'fa fa-level-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(305, 'fa fa-life-bouy', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(306, 'fa fa-life-buoy', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(307, 'fa fa-life-ring', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(308, 'fa fa-life-saver', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(309, 'fa fa-lightbulb-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(310, 'fa fa-line-chart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(311, 'fa fa-link', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(312, 'fa fa-linkedin', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(313, 'fa fa-linkedin-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(314, 'fa fa-linux', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(315, 'fa fa-list', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(316, 'fa fa-list-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(317, 'fa fa-list-ol', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(318, 'fa fa-list-ul', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(319, 'fa fa-location-arrow', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(320, 'fa fa-lock', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(321, 'fa fa-long-arrow-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(322, 'fa fa-long-arrow-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(323, 'fa fa-long-arrow-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(324, 'fa fa-long-arrow-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(325, 'fa fa-magic', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(326, 'fa fa-magnet', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(327, 'fa fa-mail-forward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(328, 'fa fa-mail-reply', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(329, 'fa fa-mail-reply-all', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(330, 'fa fa-male', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(331, 'fa fa-map-marker', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(332, 'fa fa-mars', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(333, 'fa fa-mars-double', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(334, 'fa fa-mars-stroke', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(335, 'fa fa-mars-stroke-h', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(336, 'fa fa-mars-stroke-v', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(337, 'fa fa-maxcdn', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(338, 'fa fa-meanpath', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(339, 'fa fa-medium', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(340, 'fa fa-medkit', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(341, 'fa fa-meh-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(342, 'fa fa-mercury', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(343, 'fa fa-microphone', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(344, 'fa fa-microphone-slash', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(345, 'fa fa-minus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(346, 'fa fa-minus-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(347, 'fa fa-minus-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(348, 'fa fa-minus-square-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(349, 'fa fa-mobile', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(350, 'fa fa-mobile-phone', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(351, 'fa fa-money', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(352, 'fa fa-moon-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(353, 'fa fa-mortar-board', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(354, 'fa fa-motorcycle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(355, 'fa fa-music', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(356, 'fa fa-navicon', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(357, 'fa fa-neuter', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(358, 'fa fa-newspaper-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(359, 'fa fa-openid', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(360, 'fa fa-outdent', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(361, 'fa fa-pagelines', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(362, 'fa fa-paint-brush', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(363, 'fa fa-paper-plane', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(364, 'fa fa-paper-plane-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(365, 'fa fa-paperclip', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(366, 'fa fa-paragraph', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(367, 'fa fa-paste', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(368, 'fa fa-pause', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(369, 'fa fa-paw', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(370, 'fa fa-paypal', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(371, 'fa fa-pencil', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(372, 'fa fa-pencil-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(373, 'fa fa-pencil-square-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(374, 'fa fa-phone', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(375, 'fa fa-phone-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(376, 'fa fa-photo', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(377, 'fa fa-picture-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(378, 'fa fa-pie-chart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(379, 'fa fa-pied-piper', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(380, 'fa fa-pied-piper-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(381, 'fa fa-pinterest', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(382, 'fa fa-pinterest-p', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(383, 'fa fa-pinterest-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(384, 'fa fa-plane', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(385, 'fa fa-play', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(386, 'fa fa-play-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(387, 'fa fa-play-circle-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(388, 'fa fa-plug', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(389, 'fa fa-plus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(390, 'fa fa-plus-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(391, 'fa fa-plus-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(392, 'fa fa-plus-square-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(393, 'fa fa-power-off', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(394, 'fa fa-print', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(395, 'fa fa-puzzle-piece', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(396, 'fa fa-qq', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(397, 'fa fa-qrcode', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(398, 'fa fa-question', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(399, 'fa fa-question-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(400, 'fa fa-quote-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(401, 'fa fa-quote-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(402, 'fa fa-ra', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(403, 'fa fa-random', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(404, 'fa fa-rebel', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(405, 'fa fa-recycle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(406, 'fa fa-reddit', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(407, 'fa fa-reddit-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(408, 'fa fa-refresh', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(409, 'fa fa-remove', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(410, 'fa fa-renren', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(411, 'fa fa-reorder', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(412, 'fa fa-repeat', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(413, 'fa fa-reply', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(414, 'fa fa-reply-all', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(415, 'fa fa-retweet', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(416, 'fa fa-rmb', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(417, 'fa fa-road', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(418, 'fa fa-rocket', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(419, 'fa fa-rotate-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(420, 'fa fa-rotate-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(421, 'fa fa-rouble', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(422, 'fa fa-rss', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(423, 'fa fa-rss-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(424, 'fa fa-rub', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(425, 'fa fa-ruble', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(426, 'fa fa-rupee', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(427, 'fa fa-save', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(428, 'fa fa-scissors', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(429, 'fa fa-search', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(430, 'fa fa-search-minus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(431, 'fa fa-search-plus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(432, 'fa fa-sellsy', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(433, 'fa fa-send', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(434, 'fa fa-send-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(435, 'fa fa-server', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(436, 'fa fa-share', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(437, 'fa fa-share-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(438, 'fa fa-share-alt-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(439, 'fa fa-share-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(440, 'fa fa-share-square-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(441, 'fa fa-shekel', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(442, 'fa fa-sheqel', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(443, 'fa fa-shield', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(444, 'fa fa-ship', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(445, 'fa fa-shirtsinbulk', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(446, 'fa fa-shopping-cart', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(447, 'fa fa-sign-in', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(448, 'fa fa-sign-out', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(449, 'fa fa-signal', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(450, 'fa fa-simplybuilt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(451, 'fa fa-sitemap', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(452, 'fa fa-skyatlas', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(453, 'fa fa-skype', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(454, 'fa fa-slack', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(455, 'fa fa-sliders', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(456, 'fa fa-slideshare', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(457, 'fa fa-smile-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(458, 'fa fa-soccer-ball-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(459, 'fa fa-sort', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(460, 'fa fa-sort-alpha-asc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(461, 'fa fa-sort-alpha-desc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(462, 'fa fa-sort-amount-asc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(463, 'fa fa-sort-amount-desc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(464, 'fa fa-sort-asc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(465, 'fa fa-sort-desc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(466, 'fa fa-sort-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(467, 'fa fa-sort-numeric-asc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(468, 'fa fa-sort-numeric-desc', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(469, 'fa fa-sort-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(470, 'fa fa-soundcloud', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(471, 'fa fa-space-shuttle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(472, 'fa fa-spinner', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(473, 'fa fa-spoon', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(474, 'fa fa-spotify', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(475, 'fa fa-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(476, 'fa fa-square-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(477, 'fa fa-stack-exchange', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(478, 'fa fa-stack-overflow', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(479, 'fa fa-star', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(480, 'fa fa-star-half', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(481, 'fa fa-star-half-empty', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(482, 'fa fa-star-half-full', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(483, 'fa fa-star-half-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(484, 'fa fa-star-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(485, 'fa fa-steam', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(486, 'fa fa-steam-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(487, 'fa fa-step-backward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(488, 'fa fa-step-forward', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(489, 'fa fa-stethoscope', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(490, 'fa fa-stop', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(491, 'fa fa-street-view', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(492, 'fa fa-strikethrough', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(493, 'fa fa-stumbleupon', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(494, 'fa fa-stumbleupon-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(495, 'fa fa-subscript', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(496, 'fa fa-subway', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(497, 'fa fa-suitcase', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(498, 'fa fa-sun-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(499, 'fa fa-superscript', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(500, 'fa fa-support', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(501, 'fa fa-table', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(502, 'fa fa-tablet', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(503, 'fa fa-tachometer', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(504, 'fa fa-tag', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(505, 'fa fa-tags', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(506, 'fa fa-tasks', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(507, 'fa fa-taxi', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(508, 'fa fa-tencent-weibo', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(509, 'fa fa-terminal', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(510, 'fa fa-text-height', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(511, 'fa fa-text-width', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(512, 'fa fa-th', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(513, 'fa fa-th-large', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(514, 'fa fa-th-list', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(515, 'fa fa-thumb-tack', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(516, 'fa fa-thumbs-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(517, 'fa fa-thumbs-o-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(518, 'fa fa-thumbs-o-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(519, 'fa fa-thumbs-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(520, 'fa fa-ticket', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(521, 'fa fa-times', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(522, 'fa fa-times-circle', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(523, 'fa fa-times-circle-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(524, 'fa fa-tint', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(525, 'fa fa-toggle-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(526, 'fa fa-toggle-left', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(527, 'fa fa-toggle-off', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(528, 'fa fa-toggle-on', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(529, 'fa fa-toggle-right', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(530, 'fa fa-toggle-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(531, 'fa fa-train', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(532, 'fa fa-transgender', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(533, 'fa fa-transgender-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(534, 'fa fa-trash', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(535, 'fa fa-trash-o', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(536, 'fa fa-tree', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(537, 'fa fa-trello', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(538, 'fa fa-trophy', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(539, 'fa fa-truck', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(540, 'fa fa-try', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(541, 'fa fa-tty', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(542, 'fa fa-tumblr', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(543, 'fa fa-tumblr-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(544, 'fa fa-turkish-lira', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(545, 'fa fa-twitch', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(546, 'fa fa-twitter', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(547, 'fa fa-twitter-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(548, 'fa fa-umbrella', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(549, 'fa fa-underline', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(550, 'fa fa-undo', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(551, 'fa fa-university', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(552, 'fa fa-unlink', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(553, 'fa fa-unlock', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(554, 'fa fa-unlock-alt', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(555, 'fa fa-unsorted', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(556, 'fa fa-upload', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(557, 'fa fa-usd', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(558, 'fa fa-user', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(559, 'fa fa-user-md', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(560, 'fa fa-user-plus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(561, 'fa fa-user-secret', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(562, 'fa fa-user-times', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(563, 'fa fa-users', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(564, 'fa fa-venus', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(565, 'fa fa-venus-double', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(566, 'fa fa-venus-mars', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(567, 'fa fa-viacoin', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(568, 'fa fa-video-camera', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(569, 'fa fa-vimeo-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(570, 'fa fa-vine', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(571, 'fa fa-vk', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(572, 'fa fa-volume-down', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(573, 'fa fa-volume-off', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(574, 'fa fa-volume-up', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(575, 'fa fa-warning', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(576, 'fa fa-wechat', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(577, 'fa fa-weibo', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(578, 'fa fa-weixin', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(579, 'fa fa-whatsapp', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(580, 'fa fa-wheelchair', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(581, 'fa fa-wifi', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(582, 'fa fa-windows', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(583, 'fa fa-won', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(584, 'fa fa-wordpress', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(585, 'fa fa-wrench', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(586, 'fa fa-xing', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(587, 'fa fa-xing-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(588, 'fa fa-yahoo', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(589, 'fa fa-yelp', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(590, 'fa fa-yen', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(591, 'fa fa-youtube', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(592, 'fa fa-youtube-play', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(593, 'fa fa-youtube-square', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `log_desc` varchar(255) NOT NULL,
  `log_user` varchar(25) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `log_desc`, `log_user`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 'admin view data user - Time : 29-05-2017 15:36:16', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 'admin view role user - Time : 29-05-2017 15:36:32', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(3, 'admin view data log - Time : 29-05-2017 15:36:33', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(4, 'admin view data user - Time : 29-05-2017 15:36:34', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(5, 'admin view role user - Time : 29-05-2017 15:36:34', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(6, 'admin view data role - Time : 29-05-2017 15:37:48', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(7, 'admin view role permission - Time : 29-05-2017 15:37:48', 'admin', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(8, 'admin view data module - Time : 26-09-2018 21:24:56', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(9, 'admin update data module - Time : 26-09-2018 21:25:18', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(10, 'admin view data module - Time : 26-09-2018 21:25:18', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(11, 'admin update data module - Time : 26-09-2018 21:25:46', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(12, 'admin view data module - Time : 26-09-2018 21:25:46', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(13, 'admin view data module - Time : 26-09-2018 21:25:51', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(14, 'admin view data menu - Time : 26-09-2018 21:25:54', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(15, 'admin view data module - Time : 26-09-2018 21:25:56', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(16, 'admin view data menu - Time : 26-09-2018 21:25:59', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(17, 'admin view data module - Time : 26-09-2018 21:26:01', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(18, 'admin view role user - Time : 26-09-2018 21:26:03', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(19, 'admin view data user - Time : 26-09-2018 21:27:36', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(20, 'admin view data module - Time : 26-09-2018 21:33:17', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(21, 'admin view data user - Time : 26-09-2018 21:33:18', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(22, 'admin view role user - Time : 26-09-2018 21:33:18', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(23, 'admin view data module - Time : 26-09-2018 21:37:03', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(24, 'admin view data module - Time : 26-09-2018 21:37:41', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(25, 'admin view data module - Time : 26-09-2018 21:38:46', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(26, 'admin view data user - Time : 26-09-2018 21:38:48', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(27, 'admin view role user - Time : 26-09-2018 21:38:48', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(28, 'admin view data module - Time : 26-09-2018 21:39:57', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(29, 'admin view data user - Time : 26-09-2018 21:39:59', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(30, 'admin view role user - Time : 26-09-2018 21:39:59', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(31, 'admin view role user - Time : 26-09-2018 21:40:01', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(32, 'admin view data log - Time : 26-09-2018 21:40:02', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(33, 'admin view data user - Time : 26-09-2018 21:40:03', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(34, 'admin view role user - Time : 26-09-2018 21:40:03', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(35, 'admin view role user - Time : 26-09-2018 21:40:04', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(36, 'admin view data log - Time : 26-09-2018 21:40:05', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(37, 'admin view data user - Time : 26-09-2018 21:40:06', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(38, 'admin view role user - Time : 26-09-2018 21:40:06', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(39, 'admin view role user - Time : 26-09-2018 21:40:07', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(40, 'admin view data role - Time : 26-09-2018 21:40:11', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(41, 'admin view role permission - Time : 26-09-2018 21:40:11', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(42, 'admin view data permission - Time : 26-09-2018 21:40:12', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(43, 'admin view data module - Time : 26-09-2018 21:40:47', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(44, 'admin view data module - Time : 26-09-2018 21:41:00', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(45, 'admin view data module - Time : 26-09-2018 21:41:09', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(46, 'admin view data user - Time : 26-09-2018 21:41:11', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(47, 'admin view role user - Time : 26-09-2018 21:41:11', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(48, 'admin view data module - Time : 26-09-2018 21:41:39', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(49, 'admin view data user - Time : 26-09-2018 21:41:40', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(50, 'admin view role user - Time : 26-09-2018 21:41:40', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(51, 'admin view data module - Time : 26-09-2018 21:41:53', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(52, 'admin view data user - Time : 26-09-2018 21:42:04', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(53, 'admin view role user - Time : 26-09-2018 21:42:04', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(54, 'admin view data module - Time : 26-09-2018 21:42:44', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(55, 'admin view data user - Time : 26-09-2018 21:42:46', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(56, 'admin view role user - Time : 26-09-2018 21:42:46', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(57, 'admin view data role - Time : 26-09-2018 21:42:47', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(58, 'admin view role permission - Time : 26-09-2018 21:42:47', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(59, 'admin view data permission - Time : 26-09-2018 21:42:48', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(60, 'admin view data icon - Time : 26-09-2018 21:42:49', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(61, 'admin view data module - Time : 26-09-2018 21:42:50', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(62, 'admin view data menu - Time : 26-09-2018 21:42:54', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(63, 'admin view data module - Time : 26-09-2018 21:51:36', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(64, 'admin view data user - Time : 26-09-2018 21:51:39', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(65, 'admin view role user - Time : 26-09-2018 21:51:39', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(66, 'admin view data module - Time : 26-09-2018 21:53:09', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(67, 'admin view data module - Time : 26-09-2018 21:53:30', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(68, 'admin view data user - Time : 26-09-2018 21:53:31', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(69, 'admin view role user - Time : 26-09-2018 21:53:31', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(70, 'admin view data role - Time : 26-09-2018 21:53:35', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(71, 'admin view role permission - Time : 26-09-2018 21:53:35', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(72, 'admin view data module - Time : 26-09-2018 22:07:26', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(73, 'admin view data user - Time : 26-09-2018 22:07:27', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(74, 'admin view role user - Time : 26-09-2018 22:07:27', 'admin', 'admin', '2018-09-26', 'admin', '2018-09-26'),
(75, 'admin view data module - Time : 27-09-2018 00:11:48', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(76, 'admin view data menu - Time : 27-09-2018 00:12:02', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(77, 'admin view data menu - Time : 27-09-2018 00:12:08', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(78, 'admin view data role - Time : 27-09-2018 00:12:37', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(79, 'admin view role permission - Time : 27-09-2018 00:12:37', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(80, 'admin view role permission - Time : 27-09-2018 00:12:40', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(81, 'admin view role module - Time : 27-09-2018 00:12:41', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(82, 'admin set role menu - Time : 27-09-2018 00:12:49', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(83, 'admin view data module - Time : 27-09-2018 00:12:52', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(84, 'admin view data menu - Time : 27-09-2018 00:12:54', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(85, 'admin view data module - Time : 27-09-2018 00:13:14', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(86, 'admin view data role - Time : 27-09-2018 00:13:15', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(87, 'admin view role permission - Time : 27-09-2018 00:13:15', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(88, 'admin view role permission - Time : 27-09-2018 00:13:16', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(89, 'admin view role module - Time : 27-09-2018 00:13:17', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(90, 'admin set role menu - Time : 27-09-2018 00:13:23', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(91, 'admin view data module - Time : 27-09-2018 00:13:26', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(92, 'admin view data menu - Time : 27-09-2018 00:13:28', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(93, 'admin view data menu - Time : 27-09-2018 00:13:33', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(94, 'admin delete data menu - Time : 27-09-2018 00:13:35', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(95, 'admin view data menu - Time : 27-09-2018 00:13:36', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(96, 'admin view data menu - Time : 27-09-2018 00:13:38', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(97, 'admin delete data menu - Time : 27-09-2018 00:13:42', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(98, 'admin view data menu - Time : 27-09-2018 00:13:43', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(99, 'admin view data menu - Time : 27-09-2018 00:13:45', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(100, 'admin delete data menu - Time : 27-09-2018 00:13:48', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(101, 'admin view data menu - Time : 27-09-2018 00:13:48', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(102, 'admin delete data menu - Time : 27-09-2018 00:13:51', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(103, 'admin view data menu - Time : 27-09-2018 00:13:51', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(104, 'admin create data menu - Time : 27-09-2018 00:14:19', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(105, 'admin view data menu - Time : 27-09-2018 00:14:19', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(106, 'admin create data menu - Time : 27-09-2018 00:14:52', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(107, 'admin view data menu - Time : 27-09-2018 00:14:52', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(108, 'admin create data menu - Time : 27-09-2018 00:15:11', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(109, 'admin view data menu - Time : 27-09-2018 00:15:12', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(110, 'admin view data module - Time : 27-09-2018 00:15:28', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(111, 'admin create data module - Time : 27-09-2018 00:16:12', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(112, 'admin view data module - Time : 27-09-2018 00:16:13', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(113, 'admin view data menu - Time : 27-09-2018 00:16:16', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(114, 'admin create data menu - Time : 27-09-2018 00:16:40', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(115, 'admin view data menu - Time : 27-09-2018 00:16:41', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(116, 'admin create data menu - Time : 27-09-2018 00:16:57', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(117, 'admin view data menu - Time : 27-09-2018 00:16:58', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(118, 'admin view data role - Time : 27-09-2018 00:17:02', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(119, 'admin view role permission - Time : 27-09-2018 00:17:02', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(120, 'admin view role permission - Time : 27-09-2018 00:17:05', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(121, 'admin view role module - Time : 27-09-2018 00:17:08', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(122, 'admin create role module - Time : 27-09-2018 00:17:19', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(123, 'admin view role module - Time : 27-09-2018 00:17:20', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(124, 'admin set role menu - Time : 27-09-2018 00:17:28', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(125, 'admin set role menu - Time : 27-09-2018 00:17:37', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(126, 'admin view data module - Time : 27-09-2018 21:06:51', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(127, 'admin update data module - Time : 27-09-2018 21:07:02', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(128, 'admin view data module - Time : 27-09-2018 21:07:02', 'admin', 'admin', '2018-09-27', 'admin', '2018-09-27'),
(129, 'admin view data user - Time : 28-09-2018 12:07:14', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(130, 'admin view role user - Time : 28-09-2018 12:07:14', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(131, 'admin view role user - Time : 28-09-2018 12:07:15', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(132, 'admin view data user - Time : 28-09-2018 13:32:39', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(133, 'admin view role user - Time : 28-09-2018 13:32:39', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(134, 'admin view data module - Time : 28-09-2018 17:35:35', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(135, 'admin view data module - Time : 28-09-2018 17:35:43', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(136, 'admin view data menu - Time : 28-09-2018 17:36:33', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(137, 'admin update data menu - Time : 28-09-2018 17:36:54', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(138, 'admin view data menu - Time : 28-09-2018 17:36:54', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(139, 'admin view data role - Time : 28-09-2018 17:37:12', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(140, 'admin view role permission - Time : 28-09-2018 17:37:12', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(141, 'admin view role permission - Time : 28-09-2018 17:37:14', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(142, 'admin view role module - Time : 28-09-2018 17:37:15', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(143, 'admin view data user - Time : 28-09-2018 22:22:03', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(144, 'admin view role user - Time : 28-09-2018 22:22:04', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(145, 'admin view data role - Time : 28-09-2018 22:22:05', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(146, 'admin view role permission - Time : 28-09-2018 22:22:05', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(147, 'admin view data module - Time : 28-09-2018 22:22:08', 'admin', 'admin', '2018-09-28', 'admin', '2018-09-28'),
(148, 'admin view data user - Time : 29-09-2018 16:50:13', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(149, 'admin view role user - Time : 29-09-2018 16:50:13', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(150, 'admin view role user - Time : 29-09-2018 16:50:16', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(151, 'admin view data role - Time : 29-09-2018 16:50:20', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(152, 'admin view role permission - Time : 29-09-2018 16:50:20', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(153, 'admin view data module - Time : 29-09-2018 16:50:22', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(154, 'admin view data menu - Time : 29-09-2018 16:50:28', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(155, 'admin view data module - Time : 29-09-2018 16:50:31', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(156, 'admin view data menu - Time : 29-09-2018 16:50:33', 'admin', 'admin', '2018-09-29', 'admin', '2018-09-29'),
(157, 'admin view data module - Time : 30-09-2018 00:15:35', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(158, 'admin view data module - Time : 30-09-2018 00:30:51', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(159, 'admin view data menu - Time : 30-09-2018 00:30:53', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(160, 'admin update data menu - Time : 30-09-2018 00:31:06', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(161, 'admin view data menu - Time : 30-09-2018 00:31:06', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(162, 'admin update data menu - Time : 30-09-2018 00:32:03', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(163, 'admin view data menu - Time : 30-09-2018 00:32:03', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(164, 'admin update data menu - Time : 30-09-2018 00:32:36', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(165, 'admin view data menu - Time : 30-09-2018 00:32:36', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(166, 'admin view data module - Time : 30-09-2018 00:32:38', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(167, 'admin view data menu - Time : 30-09-2018 00:32:41', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(168, 'admin update data menu - Time : 30-09-2018 00:33:30', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(169, 'admin view data menu - Time : 30-09-2018 00:33:30', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(170, 'admin update data menu - Time : 30-09-2018 00:34:09', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(171, 'admin view data menu - Time : 30-09-2018 00:34:09', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(172, 'admin view data icon - Time : 30-09-2018 00:34:33', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(173, 'admin view data icon - Time : 30-09-2018 00:34:36', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(174, 'admin view data icon - Time : 30-09-2018 00:34:42', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(175, 'admin view data icon - Time : 30-09-2018 00:34:46', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(176, 'admin view data module - Time : 30-09-2018 00:34:52', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(177, 'admin view data menu - Time : 30-09-2018 00:34:54', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(178, 'admin update data menu - Time : 30-09-2018 00:35:09', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(179, 'admin view data menu - Time : 30-09-2018 00:35:09', 'admin', 'admin', '2018-09-30', 'admin', '2018-09-30'),
(180, 'admin view data module - Time : 04-12-2018 21:59:18', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(181, 'admin view data user - Time : 04-12-2018 21:59:20', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(182, 'admin view role user - Time : 04-12-2018 21:59:20', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(183, 'admin view data module - Time : 04-12-2018 21:59:21', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(184, 'admin view data role - Time : 04-12-2018 21:59:23', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(185, 'admin view role permission - Time : 04-12-2018 21:59:23', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(186, 'admin view data permission - Time : 04-12-2018 21:59:29', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(187, 'admin view data user - Time : 04-12-2018 21:59:34', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04'),
(188, 'admin view role user - Time : 04-12-2018 21:59:34', 'admin', 'admin', '2018-12-04', 'admin', '2018-12-04');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `menu_id` int(11) NOT NULL,
  `module_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `menu_title` varchar(100) NOT NULL,
  `menu_url` varchar(255) DEFAULT NULL,
  `menu_icon` varchar(50) DEFAULT NULL,
  `menu_order` int(11) NOT NULL,
  `menu_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`menu_id`, `module_id`, `parent_id`, `menu_title`, `menu_url`, `menu_icon`, `menu_order`, `menu_description`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 1, NULL, 'Modules', 'administration.modules', 'fa fa-gear', 1, NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 1, NULL, 'Users', 'administration.users', 'fa fa-user', 2, NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(4, 1, NULL, 'Roles', 'administration.roles', 'fa fa-users', 3, NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(5, 1, NULL, 'Permissions', 'administration.permissions', 'fa fa-flag-checkered', 4, NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(6, 1, NULL, 'Icons', 'administration.icons', 'fa fa-bullseye', 5, NULL, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(7, 2, NULL, 'Sektor', 'data_master.sektor', 'fa fa-map-marker', 1, NULL, 'admin', '2018-09-27', 'admin', '2018-09-30'),
(8, 2, NULL, 'Tingkat Pendidikan', 'data_master.tingkat_pendidikan', 'fa fa-building', 2, NULL, 'admin', '2018-09-27', 'admin', '2018-09-30'),
(9, 2, NULL, 'Jemaat', 'data_master.jemaat', 'fa fa-user', 3, NULL, 'admin', '2018-09-27', 'admin', '2018-09-30'),
(11, 3, NULL, 'Registrasi Cuti', 'transaksi.t_registrasi_cuti', 'fa fa-files-o', 2, NULL, 'admin', '2018-09-27', 'admin', '2019-09-19'),
(12, 4, NULL, 'Workflow', '', 'fa fa-briefcase', 1, NULL, 'admin', '2019-08-09', 'admin', '2019-08-09'),
(13, 4, 12, 'Pemberian Nama Workflow', 'workflow.p_document_type', 'fa fa-newspaper-o', 1, NULL, 'admin', '2019-08-09', 'admin', '2019-08-14'),
(14, 3, NULL, 'Laporan Registrasi Cuti', 'report.registrasi_cuti', 'fa fa-book', 3, NULL, 'admin', '2019-08-13', 'admin', '2019-08-13'),
(15, 4, 12, 'Daftar Pekerjaan Workflow', 'workflow.p_job_wf', 'fa fa-suitcase', 2, NULL, 'admin', '2019-08-14', 'admin', '2019-08-14'),
(16, 4, 12, 'Pengaturan Aliran Pekerjaan Workflow', 'workflow.p_workflow', 'fa fa-road', 3, NULL, 'admin', '2019-08-14', 'admin', '2019-08-14');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `list_no` int(11) NOT NULL,
  `module_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `is_active` varchar(1) DEFAULT NULL,
  `module_icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`module_id`, `module_name`, `list_no`, `module_description`, `created_by`, `created_date`, `updated_by`, `updated_date`, `is_active`, `module_icon`) VALUES
(1, 'Admin System', 1, '', 'admin', '2017-05-29', 'admin', '2018-09-26', 'Y', 'images/admin_system.png'),
(2, 'Data Master', 2, '', 'admin', '2017-05-29', 'admin', '2018-09-26', 'Y', 'images/data_master.png'),
(3, 'Transaksi', 4, '', 'admin', '2018-09-27', 'admin', '2019-09-18', 'Y', 'images/transaksi.png'),
(4, 'Parameter', 3, '', 'admin', '2019-08-09', 'admin', '2019-08-09', 'Y', 'images/parameter.png'),
(999, 'Inbox', 999, 'Inbox', 'admin', '2019-09-13', 'admin', '2019-09-13', 'Y', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_display_name` varchar(255) DEFAULT NULL,
  `permission_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_name`, `permission_display_name`, `permission_description`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 'can-view-user', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 'can-add-user', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(3, 'can-edit-user', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(4, 'can-delete-user', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(5, 'can-view-module', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(6, 'can-add-module', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(7, 'can-edit-module', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(8, 'can-delete-module', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(9, 'can-view-role', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(10, 'can-add-role', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(11, 'can-edit-role', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(12, 'can-delete-role', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(13, 'can-view-menu', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(14, 'can-add-menu', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(15, 'can-edit-menu', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(16, 'can-delete-menu', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(17, 'can-view-icon', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(18, 'can-add-icon', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(19, 'can-edit-icon', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(20, 'can-delete-icon', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(21, 'can-view-permission', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(22, 'can-add-permission', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(23, 'can-edit-permission', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(24, 'can-delete-permission', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29'),
(25, 'can-view-log', NULL, '', 'admin', '2017-05-29', 'admin', '2017-05-29');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`role_id`, `permission_id`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 1, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 2, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 3, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 4, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 5, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 6, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 7, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 8, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 9, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 10, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 11, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 12, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 13, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 14, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 15, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 16, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 17, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 18, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 19, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 20, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 21, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 22, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 23, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 24, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 25, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 1, 'admin', '2017-05-29', 'admin', '2017-05-29');

-- --------------------------------------------------------

--
-- Table structure for table `p_document_type`
--

CREATE TABLE `p_document_type` (
  `p_document_type_id` int(11) NOT NULL,
  `document_name` varchar(500) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_by` varchar(16) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_by` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `p_document_type`
--

INSERT INTO `p_document_type` (`p_document_type_id`, `document_name`, `description`, `created_date`, `created_by`, `updated_date`, `updated_by`) VALUES
(1, 'Pengajuan Cuti', '', '2019-08-09 15:26:53', 'admin', '2019-08-09 16:40:12', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `p_job_wf`
--

CREATE TABLE `p_job_wf` (
  `job_wf_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `job_wf_name` varchar(100) NOT NULL,
  `f_after_submit` varchar(100) DEFAULT NULL,
  `f_after_reject` varchar(100) DEFAULT NULL,
  `f_after_send_back` varchar(100) DEFAULT NULL,
  `file_pekerjaan` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_by` varchar(16) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_by` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `p_job_wf`
--

INSERT INTO `p_job_wf` (`job_wf_id`, `role_id`, `job_wf_name`, `f_after_submit`, `f_after_reject`, `f_after_send_back`, `file_pekerjaan`, `created_date`, `created_by`, `updated_date`, `updated_by`) VALUES
(1, 3, 'VERIFIKASI_CUTI', 'f_after_ver_cuti', NULL, NULL, 'transaksi.t_registrasi_cuti_ver', '2019-08-13 16:58:44', 'admin', '2019-08-13 17:07:18', 'admin'),
(2, 4, 'PENGESAHAN CUTI', 'f_after_pengesahan_cuti', '', NULL, 'transaksi.t_registrasi_cuti_pengesahan', '2019-09-02 17:17:40', 'admin', '2019-09-02 17:17:40', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `p_order_status`
--

CREATE TABLE `p_order_status` (
  `p_order_status_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `p_order_status`
--

INSERT INTO `p_order_status` (`p_order_status_id`, `code`, `description`) VALUES
(1, 'Mulai', 'Permohonan Dibuat'),
(2, 'Diproses', 'Permohonan Diproses'),
(3, 'Selesai', 'Permohonan Selesai'),
(4, 'Ditolak', 'Permohonan Ditolak');

-- --------------------------------------------------------

--
-- Table structure for table `p_profile_type`
--

CREATE TABLE `p_profile_type` (
  `profile_type` varchar(25) NOT NULL,
  `list_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `p_profile_type`
--

INSERT INTO `p_profile_type` (`profile_type`, `list_no`) VALUES
('FINISH', 4),
('INBOX', 1),
('OUTBOX', 2),
('REJECT', 3);

-- --------------------------------------------------------

--
-- Table structure for table `p_workflow`
--

CREATE TABLE `p_workflow` (
  `p_workflow_id` int(11) NOT NULL,
  `p_document_type_id` int(11) DEFAULT NULL,
  `order_list_no` int(11) DEFAULT NULL,
  `prev_job_wf_id` int(11) DEFAULT NULL,
  `next_job_wf_id` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_by` varchar(16) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_by` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `p_workflow`
--

INSERT INTO `p_workflow` (`p_workflow_id`, `p_document_type_id`, `order_list_no`, `prev_job_wf_id`, `next_job_wf_id`, `created_date`, `created_by`, `updated_date`, `updated_by`) VALUES
(1, 1, 1, 1, 2, '2019-08-20 19:48:48', 'admin', '2019-09-02 17:18:02', 'admin'),
(2, 1, 2, 2, 0, '2019-09-02 17:18:10', 'admin', '2019-09-02 17:18:10', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(255) NOT NULL,
  `role_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `is_active` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_description`, `created_by`, `created_date`, `updated_by`, `updated_date`, `is_active`) VALUES
(1, 'ADMINISTRATOR', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29', 'Y'),
(2, 'OPERATOR', NULL, 'admin', '2017-05-29', 'admin', '2017-05-29', 'Y'),
(3, 'VERIFIKASI CUTI', NULL, 'admin', '2019-08-13', 'admin', '2019-08-13', 'Y'),
(4, 'PENGESAHAN CUTI', NULL, 'admin', '2019-09-02', 'admin', '2019-09-02', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `role_menu`
--

CREATE TABLE `role_menu` (
  `role_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `role_menu`
--

INSERT INTO `role_menu` (`role_id`, `menu_id`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 1, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 2, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 4, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 5, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 6, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 7, 'admin', '2018-09-27', 'admin', '2018-09-27'),
(1, 8, 'admin', '2018-09-27', 'admin', '2018-09-27'),
(1, 9, 'admin', '2018-09-27', 'admin', '2018-09-27'),
(2, 2, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 12, 'admin', '2019-08-09', 'admin', '2019-08-09'),
(1, 13, 'admin', '2019-08-09', 'admin', '2019-08-09'),
(3, 14, 'admin', '2019-08-13', 'admin', '2019-08-13'),
(1, 15, 'admin', '2019-08-14', 'admin', '2019-08-14'),
(1, 16, 'admin', '2019-08-14', 'admin', '2019-08-14'),
(4, 14, 'admin', '2019-09-02', 'admin', '2019-09-02'),
(1, 11, 'admin', '2019-09-19', 'admin', '2019-09-19'),
(1, 14, 'admin', '2019-09-19', 'admin', '2019-09-19');

-- --------------------------------------------------------

--
-- Table structure for table `role_module`
--

CREATE TABLE `role_module` (
  `module_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `role_module`
--

INSERT INTO `role_module` (`module_id`, `role_id`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 1, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(1, 2, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 1, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 2, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(3, 1, 'admin', '2018-09-27', 'admin', '2018-09-27'),
(4, 1, 'admin', '2019-08-09', 'admin', '2019-08-09'),
(3, 3, 'admin', '2019-08-13', 'admin', '2019-08-13'),
(3, 4, 'admin', '2019-09-02', 'admin', '2019-09-02');

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`user_id`, `role_id`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 1, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(2, 2, 'admin', '2017-05-29', 'admin', '2017-05-29'),
(3, 3, 'admin', '2019-08-13', 'admin', '2019-08-13'),
(4, 4, 'admin', '2019-09-02', 'admin', '2019-09-02'),
(3, 4, 'admin', '2019-09-14', 'admin', '2019-09-14');

-- --------------------------------------------------------

--
-- Table structure for table `t_order`
--

CREATE TABLE `t_order` (
  `t_order_id` int(11) NOT NULL,
  `order_no` varchar(25) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `p_order_status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_order`
--

INSERT INTO `t_order` (`t_order_id`, `order_no`, `order_date`, `p_order_status_id`) VALUES
(1, '0000000001', '2019-08-09 14:30:39', 2),
(2, '0000000002', '2019-09-14 13:48:08', 4),
(3, '0000000003', '2019-09-14 13:48:17', 2),
(4, '0000000004', '2019-09-14 13:48:22', 2),
(5, '0000000005', '2019-09-14 13:48:28', 2),
(6, '0000000006', '2019-09-14 13:48:33', 2),
(7, '0000000007', '2019-09-14 13:48:38', 4),
(8, '0000000008', '2019-09-14 13:48:44', 3),
(9, '0000000009', '2019-09-20 00:58:35', 1),
(10, '0000000010', '2019-09-26 16:11:19', 2),
(11, '0000000011', '2019-09-26 16:11:53', 3),
(12, '0000000012', '2019-09-26 16:12:20', 2);

-- --------------------------------------------------------

--
-- Table structure for table `t_order_control_wf`
--

CREATE TABLE `t_order_control_wf` (
  `t_order_control_id` int(11) NOT NULL,
  `ref_order_control_id` int(11) DEFAULT NULL,
  `p_w_doc_type_id` int(11) NOT NULL,
  `p_w_job_wf_id` int(11) NOT NULL,
  `user_id_donor` int(11) DEFAULT NULL,
  `user_id_takeover` int(11) DEFAULT NULL,
  `user_id_submitter` int(11) DEFAULT NULL,
  `donor_date` datetime DEFAULT NULL,
  `taken_date` datetime DEFAULT NULL,
  `submit_date` datetime DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_no` varchar(25) DEFAULT NULL,
  `profile_type` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_order_control_wf`
--

INSERT INTO `t_order_control_wf` (`t_order_control_id`, `ref_order_control_id`, `p_w_doc_type_id`, `p_w_job_wf_id`, `user_id_donor`, `user_id_takeover`, `user_id_submitter`, `donor_date`, `taken_date`, `submit_date`, `message`, `order_id`, `order_no`, `profile_type`) VALUES
(1, NULL, 1, 1, 1, 3, 3, '2019-09-13 00:00:00', '2019-09-14 14:34:56', '2019-09-14 14:34:56', 'teruskan ke pengesahan cuti', 1, '0000000001', 'OUTBOX'),
(2, NULL, 1, 1, 1, 3, 3, '2019-09-14 00:00:00', '2019-09-14 15:38:36', '2019-09-14 15:38:36', 'formulir reject krna bnyk data tidak valid', 2, '0000000002', 'REJECT'),
(3, NULL, 1, 1, 1, NULL, NULL, '2019-09-14 00:00:00', NULL, NULL, NULL, 3, '0000000003', 'INBOX'),
(4, NULL, 1, 1, 1, NULL, NULL, '2019-09-14 00:00:00', NULL, NULL, NULL, 4, '0000000004', 'INBOX'),
(5, NULL, 1, 1, 1, NULL, NULL, '2019-09-14 00:00:00', NULL, NULL, NULL, 5, '0000000005', 'INBOX'),
(6, NULL, 1, 1, 1, NULL, NULL, '2019-09-14 00:00:00', NULL, NULL, NULL, 6, '0000000006', 'INBOX'),
(8, 1, 1, 2, 3, NULL, NULL, '2019-09-14 14:34:56', NULL, NULL, NULL, 1, '0000000001', 'INBOX'),
(11, NULL, 1, 1, 1, 3, 3, '2019-09-20 16:50:10', '2019-09-26 14:30:47', '2019-09-26 15:22:35', 'alasan tidak dapat diterima', 7, '0000000007', 'REJECT'),
(12, NULL, 1, 1, 1, 3, 3, '2019-09-20 16:50:14', '2019-09-21 01:32:22', '2019-09-26 01:13:43', NULL, 8, '0000000008', 'OUTBOX'),
(26, 12, 1, 2, 3, 4, 4, '2019-09-26 01:13:43', '2019-09-26 01:37:12', '2019-09-26 02:12:31', 'verifikasi OK', 8, '0000000008', 'FINISH'),
(27, NULL, 1, 1, 4, 3, 3, '2019-09-26 17:00:21', '2019-09-26 16:13:11', '2019-09-26 16:34:13', '** Send back job from pengesahan_cuti ** : test 123', 12, '0000000012', 'INBOX'),
(30, NULL, 1, 1, 1, 3, 3, '2019-09-26 17:08:46', '2019-09-26 17:09:28', '2019-09-26 17:10:20', NULL, 11, '0000000011', 'OUTBOX'),
(31, 30, 1, 2, 3, 4, 4, '2019-09-26 17:10:20', '2019-09-26 17:11:07', '2019-09-26 17:12:04', 'test 123', 11, '0000000011', 'FINISH'),
(34, NULL, 1, 1, 4, 3, 3, '2019-09-27 03:22:31', '2019-09-27 02:43:55', '2019-09-27 03:19:44', '** Send back job from pengesahan_cuti ** : masih terdapat kekeliruan pd NIP | ** Send back job from pengesahan_cuti ** : verifikasi belum benar, mohon diperiksa kembali', 10, '0000000010', 'INBOX');

-- --------------------------------------------------------

--
-- Table structure for table `t_registrasi_cuti`
--

CREATE TABLE `t_registrasi_cuti` (
  `t_registrasi_cuti_id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(10) NOT NULL,
  `nama_pemohon` varchar(100) NOT NULL,
  `nip` varchar(12) NOT NULL,
  `jumlah_hari_cuti` int(11) NOT NULL,
  `alasan_cuti` varchar(255) NOT NULL,
  `is_verified` varchar(1) DEFAULT 'N',
  `verified_by` varchar(50) DEFAULT NULL,
  `verified_nip` varchar(12) DEFAULT NULL,
  `verified_notes` varchar(255) DEFAULT NULL,
  `is_accepted` varchar(1) DEFAULT 'N',
  `created_date` date NOT NULL,
  `created_by` varchar(25) NOT NULL,
  `updated_date` date NOT NULL,
  `updated_by` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `t_registrasi_cuti`
--

INSERT INTO `t_registrasi_cuti` (`t_registrasi_cuti_id`, `order_no`, `nama_pemohon`, `nip`, `jumlah_hari_cuti`, `alasan_cuti`, `is_verified`, `verified_by`, `verified_nip`, `verified_notes`, `is_accepted`, `created_date`, `created_by`, `updated_date`, `updated_by`) VALUES
(1, '0000000001', 'Lina Buchanan', '196718831679', 9, 'Keperluan keluarga', 'N', NULL, NULL, NULL, 'N', '2019-09-02', 'admin', '2019-09-02', 'admin'),
(2, '0000000002', 'Charlotte Ingram', '196618491372', 6, 'Keperluan keluarga', 'N', NULL, NULL, NULL, 'N', '2019-09-02', 'admin', '2019-09-02', 'admin'),
(3, '0000000003', 'Ruby Wells', '196314821864', 6, 'Keperluan keluarga', 'N', NULL, NULL, NULL, 'N', '2019-09-02', 'admin', '2019-09-02', 'admin'),
(4, '0000000004', 'Lina Saunders', '197014941943', 10, 'Keperluan keluarga', 'N', NULL, NULL, NULL, 'N', '2019-09-02', 'admin', '2019-09-02', 'admin'),
(5, '0000000005', 'Margaret Clayton', '197017581269', 6, 'Keperluan keluarga', 'N', NULL, NULL, NULL, 'N', '2019-09-02', 'admin', '2019-09-02', 'admin'),
(6, '0000000006', 'George Hale', '197417861287', 4, 'Keperluan keluarga', 'N', NULL, NULL, NULL, 'N', '2019-09-02', 'admin', '2019-09-02', 'admin'),
(7, '0000000007', 'Helena Lewis', '198318110002', 3, 'Sakit', 'N', 'verifikasi_cuti', '198722149816', 'Alasan cuti tidak dapat diterima', 'N', '2019-09-19', 'admin', '2019-09-26', 'verifikasi_cuti'),
(8, '0000000008', 'Evan Hubbard', '197718720032', 3, 'Sakit', 'Y', 'verifikasi_cuti', '198722149816', 'test111', 'Y', '2019-09-19', 'admin', '2019-09-26', 'pengesahan_cuti'),
(9, '0000000009', 'Wiliam Decosta', '198723114053', 30, 'Life is wonderful, so I wanna enjoy it...', 'N', NULL, NULL, NULL, 'N', '2019-09-20', 'admin', '2019-09-20', 'admin'),
(10, '0000000010', 'Umar Abdul Jabar', '199012781743', 3, 'Liburan', 'Y', 'verifikasi_cuti', '198722149816', 'lanjut', 'N', '2019-09-26', 'admin', '2019-09-27', 'verifikasi_cuti'),
(11, '0000000011', 'Tiffano Isya Geri', '199011249097', 5, 'Mendampingi Istri', 'Y', 'verifikasi_cuti', '198722149816', 'Done', 'Y', '2019-09-26', 'admin', '2019-09-26', 'pengesahan_cuti'),
(12, '0000000012', 'Ninoy Harun', '198477819091', 10, 'Ngurusin Anak', 'Y', 'verifikasi_cuti', '198722149816', 'test123', 'N', '2019-09-26', 'admin', '2019-09-26', 'verifikasi_cuti');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_full_name` varchar(255) NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL,
  `created_by` varchar(25) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `updated_by` varchar(25) DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `user_status` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_full_name`, `user_email`, `user_password`, `created_by`, `created_date`, `updated_by`, `updated_date`, `user_status`) VALUES
(1, 'admin', 'Administrator', 'admin@gmail.com', '0192023a7bbd73250516f069df18b500', 'admin', '2017-05-29', 'admin', '2017-05-29', '1'),
(2, 'operator', 'Operator', 'operator@none.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'admin', '2017-05-29', 'admin', '2017-05-29', '1'),
(3, 'verifikasi_cuti', 'Verifikator Cuti', 'verifikatorcuti@none.com', '8007e7c993dd8d31d7e031cb477defda', 'admin', '2019-08-13', 'admin', '2019-08-13', '1'),
(4, 'pengesahan_cuti', 'Pengesahan Cuti', 'pengesahancuti@gmail.com', '0da56746c5f341832eeabfe50c0ccaf9', 'admin', '2019-09-02', 'admin', '2019-09-02', '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `icons`
--
ALTER TABLE `icons`
  ADD PRIMARY KEY (`icon_id`),
  ADD UNIQUE KEY `icons_pk` (`icon_id`) USING BTREE;

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD UNIQUE KEY `logs_pk` (`log_id`) USING BTREE;

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`menu_id`),
  ADD UNIQUE KEY `menus_pk` (`menu_id`) USING BTREE,
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `r10_fk` (`module_id`) USING BTREE;

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`module_id`),
  ADD UNIQUE KEY `modules_pk` (`module_id`) USING BTREE;

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permissions_pk` (`permission_id`) USING BTREE;

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD KEY `r8_fk` (`role_id`) USING BTREE,
  ADD KEY `r9_fk` (`permission_id`) USING BTREE;

--
-- Indexes for table `p_document_type`
--
ALTER TABLE `p_document_type`
  ADD PRIMARY KEY (`p_document_type_id`);

--
-- Indexes for table `p_job_wf`
--
ALTER TABLE `p_job_wf`
  ADD PRIMARY KEY (`job_wf_id`);

--
-- Indexes for table `p_order_status`
--
ALTER TABLE `p_order_status`
  ADD PRIMARY KEY (`p_order_status_id`);

--
-- Indexes for table `p_profile_type`
--
ALTER TABLE `p_profile_type`
  ADD PRIMARY KEY (`profile_type`);

--
-- Indexes for table `p_workflow`
--
ALTER TABLE `p_workflow`
  ADD PRIMARY KEY (`p_workflow_id`),
  ADD KEY `fk_wf8` (`p_document_type_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `roles_pk` (`role_id`) USING BTREE;

--
-- Indexes for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD KEY `r11_fk` (`role_id`) USING BTREE,
  ADD KEY `r12_fk` (`menu_id`) USING BTREE;

--
-- Indexes for table `role_module`
--
ALTER TABLE `role_module`
  ADD KEY `role_id` (`role_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `t_order`
--
ALTER TABLE `t_order`
  ADD PRIMARY KEY (`t_order_id`);

--
-- Indexes for table `t_order_control_wf`
--
ALTER TABLE `t_order_control_wf`
  ADD PRIMARY KEY (`t_order_control_id`);

--
-- Indexes for table `t_registrasi_cuti`
--
ALTER TABLE `t_registrasi_cuti`
  ADD UNIQUE KEY `t_registrasi_cuti_id` (`t_registrasi_cuti_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_pk` (`user_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `p_document_type`
--
ALTER TABLE `p_document_type`
  MODIFY `p_document_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `p_job_wf`
--
ALTER TABLE `p_job_wf`
  MODIFY `job_wf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `p_workflow`
--
ALTER TABLE `p_workflow`
  MODIFY `p_workflow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `t_order`
--
ALTER TABLE `t_order`
  MODIFY `t_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `t_order_control_wf`
--
ALTER TABLE `t_order_control_wf`
  MODIFY `t_order_control_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `t_registrasi_cuti`
--
ALTER TABLE `t_registrasi_cuti`
  MODIFY `t_registrasi_cuti_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`menu_id`),
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`);

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`),
  ADD CONSTRAINT `permission_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `p_workflow`
--
ALTER TABLE `p_workflow`
  ADD CONSTRAINT `fk_wf8` FOREIGN KEY (`p_document_type_id`) REFERENCES `p_document_type` (`p_document_type_id`);

--
-- Constraints for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD CONSTRAINT `role_menu_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`),
  ADD CONSTRAINT `role_menu_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `role_module`
--
ALTER TABLE `role_module`
  ADD CONSTRAINT `role_module_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `role_module_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`);

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `role_user_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
