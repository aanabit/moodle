@core @core_contentbank @contentbank_h5p @javascript
Feature: Sort content in the content bank
  In order to temporarily organise the content of the content bank
  As an admin
  I need to be able to sort the content bank in various ways

  Background:
    Given the following "contentbank content" exist:
        | contextlevel | reference | contenttype       | user  | contentname          |
        | System       |           | contenttype_h5p   | admin | filltheblanks.h5p    |
        | System       |           | contenttype_h5p   | admin | mathsbook.h5p        |

  Scenario: Admins can order content in the content bank
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Navigation" block if not present
    And I expand "Site pages" node
    And I click on "Content bank" "link"
    When I click on "Display contentbank with file details" "button"
    And I should see "Last modified"
    And I follow "filltheblanks.h5p"
    And I click on "Content bank" "link"
    And I should see "Last modified"
    And I click on "Display contentbank with icons" "button"
    And I follow "filltheblanks.h5p"
    And I click on "Content bank" "link"
    And I should not see "Last modified"

