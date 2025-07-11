import { Type, Cache, Dom } from 'main.core';

import { type Slider } from './slider';
import { type LabelOptions } from './types/label-options';

export class Label
{
	static MIN_LEFT_OFFSET = 25;
	static MIN_TOP_OFFSET = 17;
	static INTERVAL_TOP_OFFSET = 50;

	slider: Slider = null;
	color = null;
	bgColor = null;
	iconClass = '';
	iconTitle = '';
	onclick = null;
	text = null;
	cache = new Cache.MemoryCache();

	constructor(slider: Slider, labelOptions: LabelOptions)
	{
		this.slider = slider;
		const options = Type.isPlainObject(labelOptions) ? labelOptions : {};
		this.setBgColor(options.bgColor);
		this.setColor(options.color);
		this.setText(options.text);
		this.setIconClass(options.iconClass);
		this.setIconTitle(options.iconTitle);
		this.setOnclick(options.onclick);
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {
			return Dom.create('div', {
				props: {
					className: 'side-panel-label',
				},
				children: [
					this.getIconBox(),
					this.getTextContainer(),
				],
				events: {
					click: this.#handleClick.bind(this),
				},
			});
		});
	}

	adjustLayout(): void
	{
		const overlayRect = this.getSlider().getOverlay().getBoundingClientRect();
		const containerRect = this.getSlider().getContainer().getBoundingClientRect();
		const maxWidth = containerRect.left - overlayRect.left;

		if (maxWidth <= this.getSlider().getMinLeftBoundary())
		{
			this.hideText();
		}
		else
		{
			this.showText();
		}

		Dom.style(this.getContainer(), 'max-width', `${maxWidth - this.constructor.MIN_LEFT_OFFSET}px`);
	}

	getIconBox(): HTMLElement
	{
		return this.cache.remember('icon-box', () => {
			return Dom.create('div', {
				props: {
					className: 'side-panel-label-icon-box',
				},
				children: [
					this.getIconContainer(),
				],
			});
		});
	}

	getIconContainer(): HTMLElement
	{
		return this.cache.remember('icon-container', () => {
			return Dom.create('div', {
				props: {
					className: `side-panel-label-icon ${this.getIconClass()}`,
				},
			});
		});
	}

	#handleClick(event: MouseEvent): void
	{
		event.stopPropagation();

		const fn = this.getOnclick();
		if (fn)
		{
			fn(this, this.getSlider());
		}
	}

	showIcon(): void
	{
		Dom.removeClass(this.getContainer(), 'side-panel-label-icon--hide');
	}

	hideIcon(): void
	{
		Dom.addClass(this.getContainer(), 'side-panel-label-icon--hide');
	}

	darkenIcon(): void
	{
		Dom.addClass(this.getContainer(), 'side-panel-label-icon--darken');
	}

	lightenIcon(): void
	{
		Dom.removeClass(this.getContainer(), 'side-panel-label-icon--darken');
	}

	hideText(): void
	{
		Dom.addClass(this.getTextContainer(), 'side-panel-label-text-hidden');
	}

	showText(): void
	{
		Dom.removeClass(this.getTextContainer(), 'side-panel-label-text-hidden');
	}

	isTextHidden(): boolean
	{
		return Dom.hasClass(this.getTextContainer(), 'side-panel-label-text-hidden');
	}

	getTextContainer(): HTMLElement
	{
		return this.cache.remember('text-container', () => {
			return Dom.create('span', {
				props: {
					className: 'side-panel-label-text',
				},
			});
		});
	}

	setColor(color: string): void
	{
		if (Type.isStringFilled(color))
		{
			this.color = color;

			Dom.style(this.getTextContainer(), 'color', color);
		}
	}

	getColor(): string | null
	{
		return this.color;
	}

	setBgColor(color: string | [string, number], opacity: number): void
	{
		let bgColor: string = Type.isArray(color) ? color[0] : color;
		let alfa: number = Type.isArray(color) ? color[1] : opacity;

		if (Type.isStringFilled(bgColor))
		{
			const matches = bgColor.match(/^#([\dA-Fa-f]{6}|[\dA-Fa-f]{3})$/);
			if (matches)
			{
				let hex = matches[1];
				if (hex.length === 3)
				{
					hex = hex.replaceAll(/([\da-f])/gi, '$1$1');
				}

				alfa = Type.isNumber(alfa) && alfa >= 0 && alfa <= 100 ? alfa : 95;
				const alfaHex = `0${Math.round(255 * (alfa / 100)).toString(16)}`.slice(-2).toUpperCase();

				bgColor = `#${hex}${alfaHex}`;
			}

			this.bgColor = bgColor;
			Dom.style(this.getContainer(), 'background-color', bgColor);
		}
		else if (bgColor === null)
		{
			this.bgColor = null;
			Dom.style(this.getContainer(), 'background-color', null);
		}
	}

	getBgColor(): string | null
	{
		return this.bgColor;
	}

	setText(text: string | null): void
	{
		if (Type.isStringFilled(text))
		{
			this.text = text;
			this.getTextContainer().textContent = text;
		}
		else if (text === null)
		{
			this.text = text;
			this.getTextContainer().textContent = '';
		}
	}

	getText(): string | null
	{
		return this.text;
	}

	setIconClass(iconClass: string | null): void
	{
		if (Type.isStringFilled(iconClass))
		{
			Dom.removeClass(this.getIconContainer(), this.iconClass);
			this.iconClass = iconClass;
			Dom.addClass(this.getIconContainer(), this.iconClass);
		}
		else if (iconClass === null)
		{
			Dom.removeClass(this.getIconContainer(), this.iconClass);
			this.iconClass = iconClass;
		}
	}

	getIconClass(): string
	{
		return this.iconClass;
	}

	setIconTitle(iconTitle: string | null): void
	{
		if (Type.isStringFilled(iconTitle) || iconTitle === null)
		{
			Dom.attr(this.getIconBox(), 'title', iconTitle);
			this.iconTitle = iconTitle;
		}
	}

	getIconTitle(): void
	{
		return this.iconTitle;
	}

	setOnclick(fn: Function): void
	{
		if (Type.isFunction(fn) || fn === null)
		{
			this.onclick = fn;
		}
	}

	getOnclick(): string | null
	{
		return this.onclick;
	}

	getSlider(): Slider
	{
		return this.slider;
	}

	moveAt(position: number): void
	{
		if (Type.isNumber(position) && position >= 0)
		{
			Dom.style(
				this.getSlider().getLabelsContainer(),
				'top',
				`${this.constructor.MIN_TOP_OFFSET + (position * this.constructor.INTERVAL_TOP_OFFSET)}px`,
			);
		}
	}
}
