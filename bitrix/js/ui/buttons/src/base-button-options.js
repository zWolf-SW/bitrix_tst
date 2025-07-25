import ButtonTag from './button/button-tag';
import BaseButton from './base-button';
import type { SetCounterOptions } from './base-button';

export type BaseButtonOptions = {
	useAirDesign?: boolean,
	tag?: ButtonTag,
	baseClass?: string,
	text?: string,
	props?: { [key: string]: string },
	counter?: number | string,
	leftCounter?: SetCounterOptions,
	rightCounter?: SetCounterOptions,
	link?: string,
	maxWidth?: number,
	className?: string,
	disabled?: boolean,
	onclick?: (button: BaseButton, event: MouseEvent) => {},
	events?: { [event: string]: (button: BaseButton, event: MouseEvent) => {} },
	dataset?: { [key: string]: string },
};
