// resources/js/Components/ChartGrid.jsx

import React from 'react';

/**
 * ChartGrid Component
 * A reusable container component for displaying charts or other content panels.
 * Provides a consistent visual style with a title.
 *
 * @param {object} props
 * @param {string} props.title - The title to display at the top of the grid.
 * @param {React.ReactNode} props.children - The content to be rendered inside the grid.
 */
const ChartGrid = ({ title, children }) => {
    return (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            {/* Panel Header with Title */}
            <div className="p-4 border-b border-gray-200 font-semibold text-lg">
                {title}
            </div>

            {/* Panel Content */}
            <div className="p-4">
                {children}
            </div>
        </div>
    );
};

export default ChartGrid;