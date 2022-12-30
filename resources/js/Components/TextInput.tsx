import { forwardRef, useEffect, useRef } from 'react';

interface Props extends React.InputHTMLAttributes<HTMLInputElement> {
    isFocused?: boolean;
    handleChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
}

export default forwardRef(function TextInput(
    { type = 'text', name, id, value, className, autoComplete, required, isFocused, handleChange } : Props,
    ref: React.ForwardedRef<HTMLInputElement>,
) {
    const input = ref ? ref : useRef<HTMLInputElement | null>(null);

    useEffect(() => {
        if (isFocused) {
            // @ts-ignore - This is a valid ref
            input.current?.focus();
        }
    }, []);

    return (
        <div className="flex flex-col items-start">
            <input
                type={type}
                name={name}
                id={id}
                value={value}
                className={
                    `border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ` +
                    className
                }
                ref={input}
                autoComplete={autoComplete}
                required={required}
                onChange={(e) => handleChange(e)}
            />
        </div>
    );
});
