@core @core_message @javascript
Feature: Create conversations for course's groups
  In order to manage a course group in a course
  As a user
  I need to be able to ensure group conversations reflect the memberships of course groups

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student0 | Student   | 0        | student0@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
      | student4 | Student   | 4        | student4@example.com |
    And the following "course enrolments" exist:
      | user     | course | role |
      | teacher1 | C1     | editingteacher |
      | student0 | C1     | student |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | student3 | C1     | student |
      | student4 | C1     | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > Groups" in current page administration
    And I press "Create group"
    And I set the following fields to these values:
      | Group name      | Big Group |
      | Group messaging | 1         |
    And I press "Save changes"
    And I press "Create group"
    And I set the following fields to these values:
      | Group name      | Small Group |
      | Group messaging | 1           |
    And I press "Save changes"
    And I press "Create group"
    And I set the following fields to these values:
      | Group name      | Quiet Group |
      | Group messaging | 0           |
    And I press "Save changes"
    And I add "Teacher 1 (teacher1@example.com)" user to "Big Group" group members
    And I add "Student 0 (student0@example.com)" user to "Big Group" group members
    And I add "Student 1 (student1@example.com)" user to "Big Group" group members
    And I add "Student 2 (student2@example.com)" user to "Big Group" group members
    And I add "Student 3 (student3@example.com)" user to "Big Group" group members
    And I add "Teacher 1 (teacher1@example.com)" user to "Small Group" group members
    And I add "Teacher 1 (teacher1@example.com)" user to "Quiet Group" group members
    And I add "Student 0 (student0@example.com)" user to "Quiet Group" group members

  Scenario: View only your groups' conversations
    Given I open messaging
    Then I should see "Big Group" in the "[data-region='view-overview-group-messages']" "css_element"
    And I should see "Small Group" in the "[data-region='view-overview-group-messages']" "css_element"
    And I should not see "Quiet Group" in the "[data-region='view-overview-group-messages']" "css_element"
    And I log out
    And I log in as "student1"
    And I open messaging
    And I should see "Big Group" in the "[data-region='view-overview-group-messages']" "css_element"
    And I should not see "Small Group" in the "[data-region='view-overview-group-messages']" "css_element"
    And I should not see "Quiet Group" in the "[data-region='view-overview-group-messages']" "css_element"

  Scenario: View group conversation's participants numbers
    Given I open messaging
    Then I select "Big Group" conversation in messaging
    And I should see "5 participants" in the "[data-region='message-drawer']" "css_element"
    And I go back in "view-conversation" message drawer
    And I expand "Group" group in "view-overview-group-messages"
    And I select "Small Group" conversation in messaging
    And I should see "1 participants" in the "[data-region='message-drawer']" "css_element"

  Scenario: View group conversation's participants list
    Given I open messaging
    Then I select "Big Group" conversation in messaging
    And I click on "[data-action='view-group-info']" "css_element"
    And I should not see "Teacher 1" in the "[data-region='group-info-content-container']" "css_element"
    And I should see "Student 0" in the "[data-region='group-info-content-container']" "css_element"
    And I should see "Student 1" in the "[data-region='group-info-content-container']" "css_element"
    And I should see "Student 2" in the "[data-region='group-info-content-container']" "css_element"
    And I should see "Student 3" in the "[data-region='group-info-content-container']" "css_element"
    And I should not see "Student 4" in the "[data-region='group-info-content-container']" "css_element"
    And I go back in "group-info-content-container" message drawer
    And I go back in "view-conversation" message drawer
    And I expand "Group" group in "view-overview-group-messages"
    And I select "Small Group" conversation in messaging
    And I click on "[data-action='view-group-info']" "css_element"
    And I should not see "Teacher 1" in the "[data-region='group-info-content-container']" "css_element"
    And I should see "No participants" in the "[data-region='group-info-content-container']" "css_element"
    And I should not see "Student 4" in the "[data-region='group-info-content-container']" "css_element"
    And I am on "Course 1" course homepage
    And I navigate to "Users > Groups" in current page administration
    And I add "Student 4 (student4@example.com)" user to "Big Group" group members
    And I add "Student 4 (student4@example.com)" user to "Small Group" group members
    And I open messaging
    And I select "Big Group" conversation in messaging
    And I should see "6 participants" in the "[data-region='message-drawer']" "css_element"
    And I click on "[data-action='view-group-info']" "css_element"
    And I should see "Student 4" in the "[data-region='group-info-content-container']" "css_element"
    And I go back in "group-info-content-container" message drawer
    And I go back in "view-conversation" message drawer
    And I expand "Group" group in "view-overview-group-messages"
    And I select "Small Group" conversation in messaging
    And I should see "2 participants" in the "[data-region='message-drawer']" "css_element"
    And I click on "[data-action='view-group-info']" "css_element"
    And I should not see "No participants" in the "[data-region='group-info-content-container']" "css_element"
    And I should see "Student 4" in the "[data-region='group-info-content-container']" "css_element"
