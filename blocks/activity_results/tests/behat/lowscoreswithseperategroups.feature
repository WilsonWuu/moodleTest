@block @block_activity_results
Feature: The activity results block displays students in separate groups scores
  In order to be display student scores
  As a user
  I need to see the activity results block

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
      | student1 | Student | 1 | student1@example.com | S1 |
      | student2 | Student | 2 | student2@example.com | S2 |
      | student3 | Student | 3 | student3@example.com | S3 |
      | student4 | Student | 4 | student4@example.com | S4 |
      | student5 | Student | 5 | student5@example.com | S5 |
      | student6 | Student | 6 | student6@example.com | S6 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
      | Group 3 | C1 | G3 |
      | Group 4 | C1 | G4 |
      | Group 5 | C1 | G5 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
      | student4 | C1 | student |
      | student5 | C1 | student |
      | student6 | C1 | student |
    And the following "group members" exist:
      | user     | group   |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G2 |
      | student4 | G2 |
      | student5 | G3 |
      | student6 | G3 |
    And the following "activities" exist:
      | activity | course | idnumber | name            | assignsubmission_file_enabled | groupmode |
      | assign   | C1     | a1       | Test assignment | 0                             | 1         |
    And the following "grade grades" exist:
      | gradeitem       | user     | grade  |
      | Test assignment | student1 | 100.00 |
      | Test assignment | student2 | 90.00  |
      | Test assignment | student3 | 90.00  |
      | Test assignment | student4 | 80.00  |
      | Test assignment | student5 | 80.00  |
      | Test assignment | student6 | 70.00  |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: Configure the block on the course page to show 1 low score
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 1 |
      | id_config_gradeformat | Percentages |
      | id_config_nameformat | Display full names |
      | id_config_decimalpoints | 0 |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group 3" in the "Activity results" "block"
    And I should see "75%" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show 1 low score as a fraction
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 1 |
      | id_config_gradeformat | Fractions |
      | id_config_nameformat | Display full names |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group 3" in the "Activity results" "block"
    And I should see "75.00/100.00" in the "Activity results" "block"
    And I am on the "Course 1" course page logged in as student5
    And I should see "Student 6" in the "Activity results" "block"
    And I should see "70.00/100.00" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show 1 low score as a absolute numbers
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 1 |
      | id_config_gradeformat | Absolute numbers |
      | id_config_nameformat | Display full names |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group 3" in the "Activity results" "block"
    And I should see "75.00" in the "Activity results" "block"
    And I am on the "Course 1" course page logged in as student5
    And I should see "Student 6" in the "Activity results" "block"
    And I should see "70.00" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show multiple low scores as percentages
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 2 |
      | id_config_gradeformat | Percentages |
      | id_config_nameformat | Display full names |
      | id_config_decimalpoints | 0 |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group 2" in the "Activity results" "block"
    And I should see "85%" in the "Activity results" "block"
    And I should see "Group 3" in the "Activity results" "block"
    And I should see "75%" in the "Activity results" "block"
    And I am on the "Course 1" course page logged in as student5
    And I should see "Student 6" in the "Activity results" "block"
    And I should see "70%" in the "Activity results" "block"
    And I should see "Student 5" in the "Activity results" "block"
    And I should see "80%" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show multiple low scores as fractions
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 2 |
      | id_config_gradeformat | Fractions |
      | id_config_nameformat | Display full names |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group 2" in the "Activity results" "block"
    And I should see "85.00/100.00" in the "Activity results" "block"
    And I should see "Group 3" in the "Activity results" "block"
    And I should see "75.00/100.00" in the "Activity results" "block"
    And I am on the "Course 1" course page logged in as student3
    And I should see "Student 3" in the "Activity results" "block"
    And I should see "90.00/100.00" in the "Activity results" "block"
    And I should see "Student 4" in the "Activity results" "block"
    And I should see "80.00/100.00" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show multiple low scores as absolute numbers
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 2 |
      | id_config_gradeformat | Absolute numbers |
      | id_config_nameformat | Display full names |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group 2" in the "Activity results" "block"
    And I should see "85.00" in the "Activity results" "block"
    And I should see "Group 3" in the "Activity results" "block"
    And I should see "75.00" in the "Activity results" "block"
    And I am on the "Course 1" course page logged in as student5
    And I should see "Student 5" in the "Activity results" "block"
    And I should see "80.00" in the "Activity results" "block"
    And I should see "Student 6" in the "Activity results" "block"
    And I should see "70.00" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show multiple low scores using ID numbers
    Given the following config values are set as admin:
      | showuseridentity | idnumber,email |
    And I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 2 |
      | id_config_gradeformat | Percentages |
      | id_config_nameformat | Display only ID numbers |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group" in the "Activity results" "block"
    And I should see "85.00%" in the "Activity results" "block"
    And I should see "75.00%" in the "Activity results" "block"
    # Students cannot see user identity fields.
    And I am on the "Course 1" course page logged in as student1
    And I should see "User" in the "Activity results" "block"
    And I should not see "User S1" in the "Activity results" "block"
    And I should see "100.00%" in the "Activity results" "block"
    And I should not see "User S2" in the "Activity results" "block"
    And I should see "90.00%" in the "Activity results" "block"

  Scenario: Try to configure the block on the course page to show multiple low scores using anonymous names
    Given I add the "Activity results" block
    When I configure the "Activity results" block
    And I set the following fields to these values:
      | id_config_showbest | 0 |
      | id_config_showworst | 2 |
      | id_config_gradeformat | Percentages |
      | id_config_nameformat | Anonymous results |
      | id_config_usegroups | Yes |
    And I press "Save changes"
    Then I should see "Group" in the "Activity results" "block"
    And I should see "85.00%" in the "Activity results" "block"
    And I should see "75.00%" in the "Activity results" "block"
    And I am on the "Course 1" course page logged in as student1
    And I should see "User" in the "Activity results" "block"
    And I should see "100.00%" in the "Activity results" "block"
    And I should see "90.00%" in the "Activity results" "block"
