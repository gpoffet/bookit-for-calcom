/**
 * BookIt for Cal.com — Block editor component.
 *
 * @package BookIt_For_CalCom
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import {
	PanelBody,
	SelectControl,
	TextControl,
	RangeControl,
	ToggleControl,
	ColorPicker,
	Notice,
	__experimentalText as Text,
} from '@wordpress/components';

/**
 * Block edit component.
 *
 * @param {Object} props           Block props.
 * @param {Object} props.attributes Block attributes.
 * @param {Function} props.setAttributes Setter.
 * @returns {JSX.Element}
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		eventType,
		displayType,
		label,
		inlineHeight,
		theme,
		accentColor,
		hideDetails,
		prefillUser,
		btnBgColor,
		btnTextColor,
		btnBorderRadius,
	} = attributes;

	const blockProps = useBlockProps();

	// bookitEditorData is passed by wp_localize_script from class-bookit-block.php.
	const editorData = window.bookitEditorData || {};
	const eventTypes = editorData.eventTypes || [];
	const hasApiKey  = editorData.hasApiKey   || false;

	const displayTypes = [
		{ label: __( 'Popup button', 'bookit-for-calcom' ), value: 'popup-button' },
		{ label: __( 'Popup text link', 'bookit-for-calcom' ), value: 'popup-text' },
		{ label: __( 'Inline calendar', 'bookit-for-calcom' ), value: 'inline' },
	];

	const themes = [
		{ label: __( 'Use global setting', 'bookit-for-calcom' ), value: 'global' },
		{ label: __( 'Auto (follow browser)', 'bookit-for-calcom' ), value: 'auto' },
		{ label: __( 'Light', 'bookit-for-calcom' ), value: 'light' },
		{ label: __( 'Dark', 'bookit-for-calcom' ), value: 'dark' },
	];

	const configuredUsername = editorData.username || '';
	const eventOptions = eventTypes.map( ( et ) => {
		// Cal.com v2 API may return username at various levels.
		const username = et.username || et.owner?.username || et.profile?.username || configuredUsername;
		return {
			label: et.title + ' \u2014 ' + et.slug,
			value: username ? username + '/' + et.slug : et.slug,
		};
	} );

	const showLabel = 'popup-button' === displayType || 'popup-text' === displayType;

	return (
		<>
			<InspectorControls>

				{/* ── Event ── */}
				<PanelBody title={ __( 'Event', 'bookit-for-calcom' ) } initialOpen={ true }>
					{ hasApiKey && eventOptions.length > 0 ? (
						<SelectControl
							label={ __( 'Event type', 'bookit-for-calcom' ) }
							value={ eventType }
							options={ [
								{ label: __( '— Select an event —', 'bookit-for-calcom' ), value: '' },
								...eventOptions,
							] }
							onChange={ ( val ) => setAttributes( { eventType: val } ) }
						/>
					) : (
						<>
							{ ! hasApiKey && (
								<Notice status="warning" isDismissible={ false }>
									{ __( 'No API key configured. Enter the event slug manually.', 'bookit-for-calcom' ) }
								</Notice>
							) }
							<TextControl
								label={ __( 'Event slug', 'bookit-for-calcom' ) }
								help={ __( 'Format: username/event-slug', 'bookit-for-calcom' ) }
								value={ eventType }
								onChange={ ( val ) => setAttributes( { eventType: val } ) }
							/>
						</>
					) }

					<SelectControl
						label={ __( 'Display type', 'bookit-for-calcom' ) }
						value={ displayType }
						options={ displayTypes }
						onChange={ ( val ) => setAttributes( { displayType: val } ) }
					/>

					{ showLabel && (
						<TextControl
							label={ __( 'Button / link label', 'bookit-for-calcom' ) }
							value={ label }
							onChange={ ( val ) => setAttributes( { label: val } ) }
						/>
					) }

					{ 'inline' === displayType && (
						<RangeControl
							label={ __( 'Inline height (px)', 'bookit-for-calcom' ) }
							value={ inlineHeight }
							min={ 300 }
							max={ 1200 }
							step={ 50 }
							onChange={ ( val ) => setAttributes( { inlineHeight: val } ) }
						/>
					) }
				</PanelBody>

				{/* ── Cal.com options ── */}
				<PanelBody title={ __( 'Cal.com options', 'bookit-for-calcom' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Theme', 'bookit-for-calcom' ) }
						value={ theme }
						options={ themes }
						onChange={ ( val ) => setAttributes( { theme: val } ) }
					/>

					<Text as="p" weight="600" style={ { marginBottom: 8 } }>
						{ __( 'Accent color', 'bookit-for-calcom' ) }
					</Text>
					<ColorPicker
						color={ accentColor }
						onChange={ ( val ) => setAttributes( { accentColor: val } ) }
						enableAlpha={ false }
					/>

					<ToggleControl
						label={ __( 'Hide booking details', 'bookit-for-calcom' ) }
						checked={ hideDetails }
						onChange={ ( val ) => setAttributes( { hideDetails: val } ) }
					/>

					<ToggleControl
						label={ __( 'Pre-fill logged-in user data', 'bookit-for-calcom' ) }
						checked={ prefillUser }
						onChange={ ( val ) => setAttributes( { prefillUser: val } ) }
					/>
				</PanelBody>

				{/* ── Button style (popup types only) ── */}
				{ showLabel && 'popup-button' === displayType && (
					<PanelBody title={ __( 'Button style', 'bookit-for-calcom' ) } initialOpen={ false }>
						<Text as="p" weight="600" style={ { marginBottom: 8 } }>
							{ __( 'Background color', 'bookit-for-calcom' ) }
						</Text>
						<ColorPicker
							color={ btnBgColor }
							onChange={ ( val ) => setAttributes( { btnBgColor: val } ) }
							enableAlpha={ false }
						/>

						<Text as="p" weight="600" style={ { marginBottom: 8 } }>
							{ __( 'Text color', 'bookit-for-calcom' ) }
						</Text>
						<ColorPicker
							color={ btnTextColor }
							onChange={ ( val ) => setAttributes( { btnTextColor: val } ) }
							enableAlpha={ false }
						/>

						<RangeControl
							label={ __( 'Border radius (px)', 'bookit-for-calcom' ) }
							value={ btnBorderRadius }
							min={ 0 }
							max={ 50 }
							onChange={ ( val ) => setAttributes( { btnBorderRadius: val } ) }
						/>
					</PanelBody>
				) }

			</InspectorControls>

			{/* ── Editor preview via PHP ── */}
			<div { ...blockProps }>
				{ eventType ? (
					<ServerSideRender
						block="bookit/cal-booking"
						attributes={ attributes }
					/>
				) : (
					<div style={ {
						border: '2px dashed #ccc',
						padding: '24px',
						textAlign: 'center',
						color: '#888',
						borderRadius: '4px',
					} }>
						<span className="dashicons dashicons-calendar-alt" style={ { fontSize: 32, marginBottom: 8, display: 'block' } }></span>
						<strong>{ __( 'Cal.com Booking', 'bookit-for-calcom' ) }</strong>
						<p>{ __( 'Select an event type in the sidebar to configure this block.', 'bookit-for-calcom' ) }</p>
					</div>
				) }
			</div>
		</>
	);
}
