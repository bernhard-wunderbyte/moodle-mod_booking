@mod @mod_booking @booking_navigation_setting
Feature: Configure and use booking's pagination and perform filtering - as a teacher.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | T1       |
      | admin1   | Admin     | 1        | admin1@example.com   | A1       |
      | student1 | Student   | 1        | student1@example.com | S1       |
      | student2 | Student   | 2        | student2@example.com | S2       |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C1     | manager        |
      | admin1   | C1     | manager        |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name       | intro                  | bookingmanager | eventtype | Default view for booking options | Send confirmation e-mail |
      | booking  | C1     | My booking | My booking description | teacher1       | Webinar   | All bookings                     | Yes                      |
    And I create booking option "Booking Option 1" in "My booking"
    And I create booking option "Booking Option 2" in "My booking"
    And I create booking option "Booking Option 3" in "My booking"
    And I create booking option "Booking Option 4" in "My booking"
    And I create booking option "Booking Option 5" in "My booking"

  @javascript
  Scenario: Configure pagination and navigate pages with list of booking options
    Given I log in as "teacher1"
    When I am on "Course 1" course homepage
    Then I follow "My booking"
    And I should see "Booking Option 1" in the "#allbookingoptionstable_r1" "css_element"
    And I should see "Booking Option 5" in the "#allbookingoptionstable_r5" "css_element"
    And "//nav[@aria-label='Page']" "xpath_element" should not exist
    And I follow "Settings"
    And I follow "Miscellaneous settings"
    And I wait "1" seconds
    And I set the field "paginationnum" to "3"
    And I press "Save and display"
    And I wait "1" seconds
    And "//nav[@aria-label='Page']" "xpath_element" should exist
    And I should see "1" in the ".allbookingoptionstable .pagination" "css_element"
    And I should see "2" in the ".allbookingoptionstable .pagination" "css_element"
    And I should see "Booking Option 1" in the "#allbookingoptionstable_r1" "css_element"
    And I should see "Booking Option 3" in the "#allbookingoptionstable_r3" "css_element"
    And I should not see "Booking Option 4" in the ".allbookingoptionstable" "css_element"
    And I should not see "Booking Option 5" in the ".allbookingoptionstable" "css_element"
    ## Goto page 2
    ## And I click on "2" "link" in the "//nav[@aria-label='Page']" "xpath_element"
    And I click on "2" "link" in the ".allbookingoptionstable .pagination" "css_element"
    And I should see "Booking Option 4" in the "#allbookingoptionstable_r1" "css_element"
    And I should see "Booking Option 5" in the "#allbookingoptionstable_r2" "css_element"

  @javascript
  Scenario: Filter of list of booking options including if pagination
    Given I log in as "teacher1"
    When I am on "Course 1" course homepage
    Then I follow "My booking"
    And I should see "Booking Option 1" in the "#allbookingoptionstable_r1" "css_element"
    And I should see "Booking Option 5" in the "#allbookingoptionstable_r5" "css_element"
    And "//nav[@aria-label='Page']" "xpath_element" should not exist
    And I wait "1" seconds
    And I set the field "Search" in the ".allbookingoptionstable" "css_element" to "Option 4"
    Then I should see "Booking Option 4" in the "#allbookingoptionstable_r1" "css_element"
    And I set the field "Search" in the ".allbookingoptionstable" "css_element" to ""
    And I follow "Settings"
    And I follow "Miscellaneous settings"
    And I wait "1" seconds
    And I set the field "paginationnum" to "3"
    And I press "Save and display"
    And I wait "1" seconds
    And "//nav[@aria-label='Page']" "xpath_element" should exist
    And I should see "1" in the ".allbookingoptionstable .pagination" "css_element"
    And I should see "2" in the ".allbookingoptionstable .pagination" "css_element"
    And I should see "Booking Option 1" in the "#allbookingoptionstable_r1" "css_element"
    And I should see "Booking Option 3" in the "#allbookingoptionstable_r3" "css_element"
    And I should not see "Booking Option 4" in the ".allbookingoptionstable" "css_element"
    And I should not see "Booking Option 5" in the ".allbookingoptionstable" "css_element"
    And I set the field "Search" in the ".allbookingoptionstable" "css_element" to "Option 4"
    Then I should see "Booking Option 4" in the "#allbookingoptionstable_r1" "css_element"
    And "//nav[@aria-label='Page']" "xpath_element" should exist