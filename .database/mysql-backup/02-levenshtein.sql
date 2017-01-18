SET NAMES utf8;
USE `Questionnaire`;

-- core levenshtein function adapted from
-- function by Jason Rust (http://sushiduy.plesk3.freepgs.com/levenshtein.sql)
-- originally from http://codejanitor.com/wp/2007/02/10/levenshtein-distance-as-a-mysql-stored-function/
-- rewritten by Arjen Lentz for utf8, code/logic cleanup and removing HEX()/UNHEX() in favour of ORD()/CHAR()
-- Levenshtein reference: http://en.wikipedia.org/wiki/Levenshtein_distance

-- Arjen note: because the levenshtein value is encoded in a byte array, distance cannot exceed 255;
-- thus the maximum string length this implementation can handle is also limited to 255 characters.

DELIMITER $$
DROP FUNCTION IF EXISTS Levenshtein $$
CREATE FUNCTION Levenshtein(
	s1 VARCHAR(255) CHARACTER SET utf8, 
	s2 VARCHAR(255) CHARACTER SET utf8,
	m INT(11)
)
RETURNS INT
DETERMINISTIC
BEGIN
	DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
	DECLARE s1_char CHAR CHARACTER SET utf8;
	-- max strlen=255 for this function
	DECLARE cv0, cv1 VARBINARY(256);

	SET s1_len = CHAR_LENGTH(s1),
			s2_len = CHAR_LENGTH(s2),
			cv1 = 0x00,
			j = 1,
			i = 1,
			c = 0;

	IF (s1 = s2) THEN
		RETURN (0);
	ELSEIF (s1_len = 0) THEN
		RETURN (s2_len);
	ELSEIF (s2_len = 0) THEN
		RETURN (s1_len);
	END IF;

	WHILE (j <= s2_len) DO
		SET cv1 = CONCAT(cv1, CHAR(j)),
				j = j + 1;
	END WHILE;

	WHILE (i <= s1_len) DO
		SET s1_char = SUBSTRING(s1, i, 1),
				c = i,
				cv0 = CHAR(i),
				j = 1;

		WHILE (j <= s2_len) DO
			SET c = c + 1,
					cost = IF(s1_char = SUBSTRING(s2, j, 1), 0, 1);

			SET c_temp = ORD(SUBSTRING(cv1, j, 1)) + cost;
			IF (c > c_temp) THEN
				SET c = c_temp;
			END IF;

			SET c_temp = ORD(SUBSTRING(cv1, j+1, 1)) + 1;
			IF (c > c_temp) THEN
				SET c = c_temp;
			END IF;

			SET cv0 = CONCAT(cv0, CHAR(c)),
					j = j + 1;
		END WHILE;

		SET cv1 = cv0,
				i = i + 1;
	END WHILE;

	RETURN (c);
END $$

DELIMITER ;