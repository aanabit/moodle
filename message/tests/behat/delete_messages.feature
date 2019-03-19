@core @core_message @javascript
Feature: Delete messages from conversations
  In order to manage a course group in a course
  As a user
  I need to be able to delete messages from conversations

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
    And the following "group messages" exist:
      | user     | group  | message                   |
      | student1 | G1     | Hi!                       |
      | student2 | G1     | How are you?              |
      | student1 | G1     | Can somebody help me?     |
    And the following "private messages" exist:
      | user     | contact  | message       |
      | student1 | student2 | Hi!           |
      | student2 | student1 | Hello!        |
      | student1 | student2 | Are you free? |
    And the following config values are set as admin:
      | messaging | 1 |

  Scenario: Delete a message sent by the user from a group conversation
    When I log in as "student1"
    And I open messaging
    Then "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!"
    And I should see "##today##j F##" in the "Group 1" "group_message_conversation"
    And I should see "How are you?" in the "Group 1" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Delete a message sent by another user from a group conversation
    When I log in as "student1"
    And I open messaging
    Then "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I click on "How are you?" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should see "Hi!"
    And I should see "##today##j F##" in the "Group 1" "group_message_conversation"
    And I should not see "How are you?" in the "Group 1" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Cancel deleting a message from a group conversation
    Given I log in as "student1"
    When I open messaging
    Then "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Canceling deletion, so messages should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!" in the "Group 1" "group_message_conversation"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"

  Scenario: Delete two messages from a group conversation
    Given I log in as "student1"
    When I open messaging
    Then "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And I click on "How are you?" "group_message_message_content"
    And I should see "2" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!"
    And I should see "##today##j F##" in the "Group 1" "group_message_conversation"
    And I should not see "How are you?" in the "Group 1" "group_message_conversation"
    And I should see "Can somebody help me?" in the "Group 1" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Cancel deleting two messages from a group conversation
    Given I log in as "student1"
    When I open messaging
    Then "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I click on "How are you?" "group_message_message_content"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Canceling deletion, so messages should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!"
    And I should see "How are you?" in the "Group 1" "group_message_conversation"
    And I should see "2" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"

  Scenario: Delete a message sent by the user from a private conversation
    Given I log in as "student1"
    When I open messaging
    Then I should see "Private"
    And I open the "Private" conversations list
    And I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I should see "Hello!" in the "Student 2" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Delete a message sent by another user from a private conversation
    Given I log in as "student1"
    When I open messaging
    Then I should see "Private"
    And I open the "Private" conversations list
    And I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hello!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should see "Hi!"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I should not see "Hello!" in the "Student 2" "group_message_conversation"

  Scenario: Cancel deleting a message from a private conversation
    Given I log in as "student1"
    When I open messaging
    Then I should see "Private"
    And I open the "Private" conversations list
    And I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Canceling deletion, so messages should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"

  Scenario: Delete two messages from a private conversation
    Given I log in as "student1"
    When I open messaging
    Then I should see "Private"
    And I open the "Private" conversations list
    And I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And I click on "Hello!" "group_message_message_content"
    And I should see "2" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!"
    And I should not see "Hello!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I should see "Are you free?" in the "Student 2" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Cancel deleting two messages from a private conversation
    Given I log in as "student1"
    When I open messaging
    Then I should see "Private"
    And I open the "Private" conversations list
    And I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I click on "Hello!" "group_message_message_content"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Canceling deletion, so messages should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!"
    And I should see "Hello!" in the "Student 2" "group_message_conversation"
    And I should see "2" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"

  Scenario: Delete a message sent by the user from a favorite conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    Then I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I should see "Hello!" in the "Student 2" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Delete a message sent by another user from a favourite conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    Then I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hello!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should see "Hi!"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I should not see "Hello!" in the "Student 2" "group_message_conversation"

  Scenario: Cancel deleting a message from a favourite conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    Then I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Canceling deletion, so messages should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!" in the "Student 2" "group_message_conversation"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"

  Scenario: Delete two messages from a favourite conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    Then I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I should see "1" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And I click on "Hello!" "group_message_message_content"
    And I should see "2" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Deleting, so messages should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!"
    And I should not see "Hello!" in the "Student 2" "group_message_conversation"
    And I should see "##today##j F##" in the "Student 2" "group_message_conversation"
    And I should see "Are you free?" in the "Student 2" "group_message_conversation"
    And I should not see "Messages selected"

  Scenario: Cancel deleting two messages from a favourite conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    Then I should see "Student 2"
    And I select "Student 2" conversation in messaging
    And I click on "Hi!" "group_message_message_content"
    And I click on "Hello!" "group_message_message_content"
    And "Delete selected messages" "button" should exist
    And I click on "Delete selected messages" "button"
#   Canceling deletion, so messages should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!"
    And I should see "Hello!" in the "Student 2" "group_message_conversation"
    And I should see "2" in the "//*[@data-region='message-drawer']//*[@data-region='message-selected-court']" "xpath_element"
