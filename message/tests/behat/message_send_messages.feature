@core @core_message @javascript @new
Feature: Message send messages
  In order to communicate with fellow users
  As a user
  I need to be able to send a message

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "groups" exist:
      | name    | course | idnumber | enablemessaging |
      | Group 1 | C1     | G1       | 1               |
    And the following "group members" exist:
      | user     | group |
      | teacher1 | G1 |
      | student1 | G1 |
      | student2 | G1 |
    And the following config values are set as admin:
      | messaging | 1 |

#  Scenario: Send a message to a group conversation
#    Given I log in as "student1"
#    Then I open messaging
#    And "Group 1" "group_message" should exist
#    And I select "Group 1" conversation in messaging
#    And I set the field with xpath "//textarea[@data-region='send-message-txt']" to "Hi!"
#    And I click on "Send message" "button"
#    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
#    And I should see "##today##j F##"
#    And I log out
#    And I log in as "student2"
#    And I open messaging
#    And "Group 1" "group_message" should exist
#    And I select "Group 1" conversation in messaging
#    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"

  Scenario: Send a message to a starred conversation
    Given I log in as "student1"
    Then I open messaging
    And "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I click on "" "button" in the "//*[@data-region='message-drawer']//div[@data-region='header-container']" "xpath_element"
    And I click on "star" "link"
    And I go back in "view-conversation" message drawer
    And I set the field with xpath "//textarea[@data-region='send-message-txt']" to "Hi!"
    And I click on "Send message" "button"
    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
    And I should see "##today##j F##"
    And I log out
    And I log in as "student2"
    And I open messaging
    And "Group 1" "group_message" should exist
    And I select "Group 1" conversation in messaging
    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"

#  Scenario: Send a message to a private conversation to a contact
#    Given I log in as "student1"
#    Then I open messaging
#    And I select "Group 1" conversation in messaging
#    And I open messaging information
#    And I click on "Student 2" "group_message_member"
#    And I click on "" "button" in the "//*[@data-region='message-drawer']//div[@data-region='header-container']" "xpath_element"
#    And I click on "Add to contacts" "link"
#    And I click on "Add" "button"
#    And I log out
#    And I log in as "student2"
#    And I open messaging
#    And I click on "Contacts" "link"
#    And I click on "Requests" "link_or_button"
#    And I click on "Student 1 Would like to contact you" "link"
#    And I click on "Accept and add to contacts" "link_or_button"
#    And I log out
#    And I log in as "student1"
#    And I open messaging
#    And I click on "Contacts" "link"
#    And I click on "Student 2" "link" in the "//*[@data-region='message-drawer']//*[@data-section='contacts']" "xpath_element"
#    And I set the field with xpath "//textarea[@data-region='send-message-txt']" to "Hi!"
#    And I click on "Send message" "button"
#    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
#    And I should see "##today##j F##"
#    And I log out

#  Scenario: Try to send a message to a private conversation is not contact but you are allowed to send a message
#    Given I log in as "student1"
#    Then I open messaging
#    And I select "Group 1" conversation in messaging
#    And I open messaging information
#    And I click on "Student 2" "group_message_member"
#    And I set the field with xpath "//textarea[@data-region='send-message-txt']" to "Hi!"
#    And I click on "Send message" "button"
#    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
#    And I should see "##today##j F##"
#    And I log out
#    And I log in as "student2"
#    And I open messaging
#    And I select "Student 1" conversation in messaging
#    And I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
#
#  Scenario: Try to send a message to a private conversation is not contact and you are not allowed to send a message
#    Given I log in as "student1"
#    Then I open messaging
#    And I click on "//*[@data-region='message-drawer']//a[@data-route='view-settings']" "xpath_element"
#    And I click on "//label[text()[contains(.,'My contacts only')]]" "xpath_element"
#    And I log out
#    And I log in as "student2"
#    And I open messaging
#    And I select "Group 1" conversation in messaging
#    And I open messaging information
#    And I click on "Student 1" "group_message_member"
#    And I should see "You need to request Student 1 to add you as a contact to be able to message them."
