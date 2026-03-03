/**
 * BookIt for Cal.com — Block registration.
 *
 * @package BookIt_For_CalCom
 */
import { registerBlockType } from '@wordpress/blocks';
import metadata from '../block.json';
import Edit from './edit';

registerBlockType( metadata.name, {
	edit: Edit,
	// save returns null because rendering is done server-side via render.php.
	save: () => null,
} );
