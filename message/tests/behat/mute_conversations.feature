@core @core_message @javascript
Feature: Mute and unmute conversations
  In order to manage a course group in a course
  As a user
  I need to be able to mute and unmute conversations

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "groups" exist:
      | name    | course | idnumber | enablemessaging |
      | Group 1 | C1     | G1       | 1               |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1 |
      | student2 | G1 |
    And the following config values are set as admin:
      | messaging | 1 |
    And the following "private messages" exist:
      | user     | contact  | message |
      | student1 | student2 | Hi!     |

  Scenario: Mute a group conversation
    Given I log in as "student1"
    When I open messaging
    Then "Group 1" "group_message" should exist
    And "muted" "message_icon" should exist in the "Group 1" "group_message" but is hidden
    And I select "Group 1" conversation in messaging
    And "muted" "message_icon" should exist in the "[data-action='view-group-info']" "css_element" but is hidden
    And I open contact menu
    And I click on "Mute" "link" in the "[data-region='header-container']" "css_element"
    And "muted" "message_icon" should exist in the "[data-action='view-group-info']" "css_element" and be visible
    And I go back in "view-conversation" message drawer
    And "muted" "message_icon" should exist in the "Group 1" "group_message" and be visible

  Scenario: Mute a private conversation
    When I log in as "student1"
    And I open messaging
    Then I should see "Private"
    And I open the "Private" conversations list
    And I should see "Student 2"
    And "muted" "message_icon" should exist in the "Student 2" "group_message" but is hidden
    And I select "Student 2" conversation in messaging
    And "muted" "message_icon" should exist in the "[data-action='view-contact']" "css_element" but is hidden
    And I open contact menu
    And I click on "Mute" "link" in the "[data-region='header-container']" "css_element"
    And "muted" "message_icon" should exist in the "[data-action='view-contact']" "css_element" and be visible
    And I go back in "view-conversation" message drawer
    And "muted" "message_icon" should exist in the "Student 2" "group_message" and be visible
