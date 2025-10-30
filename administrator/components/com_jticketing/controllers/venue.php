<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


// Load frontend venueform model
JLoader::import('venueform', JPATH_SITE . '/components/com_jticketing/controllers');
JLoader::import('common', JPATH_SITE . '/components/com_jticketing/helpers');

/**
 * Venue controller class.
 *
 * @since  1.6
 */
class JticketingControllerVenue extends JticketingControllerVenueForm
{
}
