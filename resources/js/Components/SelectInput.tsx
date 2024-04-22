import { forwardRef, useEffect, useImperativeHandle, useRef, SelectHTMLAttributes } from 'react';

interface Option {
    value: string | number;
    label: string;
}

interface SelectInputProps extends SelectHTMLAttributes<HTMLSelectElement> {
    options: Option[];
    isFocused?: boolean;
}

export default forwardRef(function SelectInput(
    { options, className = '', isFocused = false, ...props }: SelectInputProps,
    ref
) {
    const localRef = useRef<HTMLSelectElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <select
            {...props}
            className={
                'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ' +
                className
            }
            ref={localRef}
        >
            {options.map((option) => (
                <option key={option.value} value={option.value}>
                    {option.label}
                </option>
            ))}
        </select>
    );
});
