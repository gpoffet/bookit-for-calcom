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
	__experimentalBoxControl as BoxControl,
	__experimentalSpacer as Spacer,
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
		btnBorderWidth,
		btnBorderStyle,
		btnBorderColor,
		btnPaddingTop,
		btnPaddingRight,
		btnPaddingBottom,
		btnPaddingLeft,
		btnFontSize,
		btnFontWeight,
		btnTextTransform,
		btnLetterSpacing,
		btnFullWidth,
		btnHoverBgColor,
		btnHoverTextColor,
		btnHoverBorderColor,
		btnTransitionDuration,
	} = attributes;

	const blockProps = useBlockProps();

	// bookitEditorData is passed by wp_localize_script from class-bookit-block.php.
	const editorData     = window.bookitEditorData || {};
	const eventTypes     = editorData.eventTypes    || [];
	const hasApiKey      = editorData.hasApiKey     || false;
	const globalSettings = editorData.globalSettings || {};

	// Effective values: block attribute (if set) → global setting → hardcoded fallback.
	// These are used as control values so the editor always shows a meaningful number/string.
	const effectiveInlineHeight    = inlineHeight          ?? globalSettings.inlineHeight    ?? 600;
	const effectiveBtnRadius       = btnBorderRadius       ?? globalSettings.btnRadius       ?? 4;
	const effectiveBtnBorderWidth  = btnBorderWidth        ?? globalSettings.btnBorderWidth  ?? 0;
	const effectiveBtnBorderStyle  = btnBorderStyle  || globalSettings.btnBorderStyle  || 'solid';
	const effectiveBtnPaddingTop    = btnPaddingTop    ?? globalSettings.btnPaddingTop    ?? 10;
	const effectiveBtnPaddingRight  = btnPaddingRight  ?? globalSettings.btnPaddingRight  ?? 20;
	const effectiveBtnPaddingBottom = btnPaddingBottom ?? globalSettings.btnPaddingBottom ?? 10;
	const effectiveBtnPaddingLeft   = btnPaddingLeft   ?? globalSettings.btnPaddingLeft   ?? 20;
	const effectiveBtnFontSize      = btnFontSize      ?? globalSettings.btnFontSize      ?? 14;
	const effectiveBtnLetterSpacing = btnLetterSpacing ?? globalSettings.btnLetterSpacing ?? 0;
	const effectiveBtnFullWidth     = btnFullWidth     ?? globalSettings.btnFullWidth     ?? false;
	const effectiveBtnTransition    = btnTransitionDuration ?? globalSettings.btnTransitionDuration ?? 200;

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

	/** Section title helper — small bold label to separate panel sub-sections. */
	const SectionTitle = ( { children } ) => (
		<Text
			as="p"
			weight="600"
			style={ { marginTop: 16, marginBottom: 8, textTransform: 'uppercase', fontSize: 11, color: '#757575', letterSpacing: '0.5px' } }
		>
			{ children }
		</Text>
	);

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
							placeholder={ globalSettings.label || __( 'Book a meeting', 'bookit-for-calcom' ) }
							onChange={ ( val ) => setAttributes( { label: val } ) }
						/>
					) }

					{ 'inline' === displayType && (
						<RangeControl
							label={ __( 'Inline height (px)', 'bookit-for-calcom' ) }
							value={ effectiveInlineHeight }
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

				{/* ── Button style (popup-button only) ── */}
				{ showLabel && 'popup-button' === displayType && (
					<PanelBody title={ __( 'Button style', 'bookit-for-calcom' ) } initialOpen={ false }>

						{/* — Couleurs — */}
						<SectionTitle>{ __( 'Colors', 'bookit-for-calcom' ) }</SectionTitle>

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

						{/* — Bordure — */}
						<SectionTitle>{ __( 'Border', 'bookit-for-calcom' ) }</SectionTitle>

						<RangeControl
							label={ __( 'Width (px)', 'bookit-for-calcom' ) }
							value={ effectiveBtnBorderWidth }
							min={ 0 }
							max={ 20 }
							onChange={ ( val ) => setAttributes( { btnBorderWidth: val } ) }
						/>

						{ effectiveBtnBorderWidth > 0 && (
							<>
								<SelectControl
									label={ __( 'Style', 'bookit-for-calcom' ) }
									value={ effectiveBtnBorderStyle }
									options={ [
										{ label: __( 'Solid', 'bookit-for-calcom' ), value: 'solid' },
										{ label: __( 'Dashed', 'bookit-for-calcom' ), value: 'dashed' },
										{ label: __( 'Dotted', 'bookit-for-calcom' ), value: 'dotted' },
									] }
									onChange={ ( val ) => setAttributes( { btnBorderStyle: val } ) }
								/>

								<Text as="p" weight="600" style={ { marginBottom: 8 } }>
									{ __( 'Color', 'bookit-for-calcom' ) }
								</Text>
								<ColorPicker
									color={ btnBorderColor }
									onChange={ ( val ) => setAttributes( { btnBorderColor: val } ) }
									enableAlpha={ false }
								/>
							</>
						) }

						<RangeControl
							label={ __( 'Radius (px)', 'bookit-for-calcom' ) }
							value={ effectiveBtnRadius }
							min={ 0 }
							max={ 50 }
							onChange={ ( val ) => setAttributes( { btnBorderRadius: val } ) }
						/>

						{/* — Espacement — */}
						<SectionTitle>{ __( 'Padding', 'bookit-for-calcom' ) }</SectionTitle>

						<BoxControl
							label={ __( 'Padding', 'bookit-for-calcom' ) }
							values={ {
								top:    effectiveBtnPaddingTop    + 'px',
								right:  effectiveBtnPaddingRight  + 'px',
								bottom: effectiveBtnPaddingBottom + 'px',
								left:   effectiveBtnPaddingLeft   + 'px',
							} }
							onChange={ ( val ) => setAttributes( {
								btnPaddingTop:    parseInt( val.top    ) || 0,
								btnPaddingRight:  parseInt( val.right  ) || 0,
								btnPaddingBottom: parseInt( val.bottom ) || 0,
								btnPaddingLeft:   parseInt( val.left   ) || 0,
							} ) }
						/>

						{/* — Typographie — */}
						<SectionTitle>{ __( 'Typography', 'bookit-for-calcom' ) }</SectionTitle>

						<RangeControl
							label={ __( 'Font size (px)', 'bookit-for-calcom' ) }
							value={ effectiveBtnFontSize }
							min={ 10 }
							max={ 36 }
							onChange={ ( val ) => setAttributes( { btnFontSize: val } ) }
						/>

						<SelectControl
							label={ __( 'Font weight', 'bookit-for-calcom' ) }
							value={ btnFontWeight }
							options={ [
								{ label: __( 'Default', 'bookit-for-calcom' ), value: '' },
								{ label: __( 'Normal (400)', 'bookit-for-calcom' ), value: '400' },
								{ label: __( 'Medium (500)', 'bookit-for-calcom' ), value: '500' },
								{ label: __( 'Semi-bold (600)', 'bookit-for-calcom' ), value: '600' },
								{ label: __( 'Bold (700)', 'bookit-for-calcom' ), value: '700' },
								{ label: __( 'Extra-bold (800)', 'bookit-for-calcom' ), value: '800' },
							] }
							onChange={ ( val ) => setAttributes( { btnFontWeight: val } ) }
						/>

						<SelectControl
							label={ __( 'Text transform', 'bookit-for-calcom' ) }
							value={ btnTextTransform }
							options={ [
								{ label: __( 'None', 'bookit-for-calcom' ), value: '' },
								{ label: __( 'UPPERCASE', 'bookit-for-calcom' ), value: 'uppercase' },
								{ label: __( 'lowercase', 'bookit-for-calcom' ), value: 'lowercase' },
								{ label: __( 'Capitalize', 'bookit-for-calcom' ), value: 'capitalize' },
							] }
							onChange={ ( val ) => setAttributes( { btnTextTransform: val } ) }
						/>

						<RangeControl
							label={ __( 'Letter spacing (px)', 'bookit-for-calcom' ) }
							value={ effectiveBtnLetterSpacing }
							min={ 0 }
							max={ 10 }
							step={ 0.5 }
							onChange={ ( val ) => setAttributes( { btnLetterSpacing: val } ) }
						/>

						{/* — Mise en page — */}
						<SectionTitle>{ __( 'Layout', 'bookit-for-calcom' ) }</SectionTitle>

						<ToggleControl
							label={ __( 'Full width button', 'bookit-for-calcom' ) }
							checked={ effectiveBtnFullWidth }
							onChange={ ( val ) => setAttributes( { btnFullWidth: val } ) }
						/>

						{/* — Effets au survol — */}
						<SectionTitle>{ __( 'Hover effects', 'bookit-for-calcom' ) }</SectionTitle>

						<RangeControl
							label={ __( 'Transition duration (ms)', 'bookit-for-calcom' ) }
							value={ effectiveBtnTransition }
							min={ 0 }
							max={ 500 }
							step={ 50 }
							onChange={ ( val ) => setAttributes( { btnTransitionDuration: val } ) }
						/>

						<Text as="p" weight="600" style={ { marginBottom: 8 } }>
							{ __( 'Hover background color', 'bookit-for-calcom' ) }
						</Text>
						<ColorPicker
							color={ btnHoverBgColor }
							onChange={ ( val ) => setAttributes( { btnHoverBgColor: val } ) }
							enableAlpha={ false }
						/>

						<Text as="p" weight="600" style={ { marginBottom: 8 } }>
							{ __( 'Hover text color', 'bookit-for-calcom' ) }
						</Text>
						<ColorPicker
							color={ btnHoverTextColor }
							onChange={ ( val ) => setAttributes( { btnHoverTextColor: val } ) }
							enableAlpha={ false }
						/>

						{ effectiveBtnBorderWidth > 0 && (
							<>
								<Text as="p" weight="600" style={ { marginBottom: 8 } }>
									{ __( 'Hover border color', 'bookit-for-calcom' ) }
								</Text>
								<ColorPicker
									color={ btnHoverBorderColor }
									onChange={ ( val ) => setAttributes( { btnHoverBorderColor: val } ) }
									enableAlpha={ false }
								/>
							</>
						) }

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
