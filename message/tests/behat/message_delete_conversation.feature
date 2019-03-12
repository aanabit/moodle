@core @core_message @javascript
Feature: Message delete conversations
  In order to communicate with fellow users
  As a user
  I need to be able to delete conversations

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

  Scenario: Delete a private conversation
    Given I log in as "student1"
    Then I open messaging
    And I send "Hi!" message to "Student 2" user
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Cancel deletion, so conversation should be there
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Confirm deletion, so conversation should not be there
    And I click on "//button[@data-action='confirm-delete-conversation']" "xpath_element"
    And I should not see "Hi!" in the "Student 2" "group_message_conversation"
    And I should not see "##today##j F##" in the "Student 2" "group_message_conversation"

  Scenario: Delete a starred conversation
    Given I log in as "student1"
    Then I open messaging
    And I send "Hi!" message to "Student 2" user
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I open contact menu
    And I click on "Star" "link" in the "//div[@data-region='header-container']" "xpath_element"
    And I go back in "view-conversation" message drawer
    And I open "Starred" messaging tab
    And I select "Student 2" conversation in "favourites" messaging tab
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Cancel deletion, so conversation should be there
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Confirm deletion, so conversation should not be there
    And I click on "//button[@data-action='confirm-delete-conversation']" "xpath_element"
    And I should not see "Hi!" in the "Student 2" "group_message_conversation"
    And I should not see "##today##j F##" in the "Student 2" "group_message_conversation"