import PptxGenJS from 'pptxgenjs';

// Function to convert district number to ordinal (1st, 2nd, 3rd, etc.)
const getOrdinal = (number) => {
  if (number === 1) return '1st';
  if (number === 2) return '2nd';
  if (number === 3) return '3rd';
  return `${number}th`; // For any other number (4th, 5th, etc.)
};

// Function to format and generate PowerPoint report
export const generateProgramPpt = (provinces, programs) => {
  const pptx = new PptxGenJS();

  // Iterate over each province
  provinces.forEach(province => {
    // Slide 1: Province Overview
    const firstSlide = pptx.addSlide();
    firstSlide.background = { path: `${window.location.origin}/ppd-images/ppt-bg.png` };

    // Add province name as the main title
    firstSlide.addText(province.col_province.toUpperCase(), { 
      x: '-10%', y: '42%', w: '100%', fontSize: 44, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
    });

    // Add "TARGET AND ACCOMPLISHMENT" below the province name
    firstSlide.addText('TARGET AND ACCOMPLISHMENT', { 
      x: '-10%', y: '52%', w: '100%', fontSize: 28, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
    });

    // Add current date for the slide in long format
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const currentDate = new Date().toLocaleDateString(undefined, options);
    firstSlide.addText(`As of ${currentDate}`, { 
      x: '-10%', y: '62%', w: '100%', fontSize: 22, bold: true, color: '0000FF', fontFace: 'Arial', align: 'center' 
    });
 // Slide 2: Total Utilization Summary for the Province
 const provinceUtilizationSlide = pptx.addSlide();
 provinceUtilizationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-total.png` };

 // Calculate total fund utilized for all cities in the province
 let totalFundUtilizedPerProvince = 0;

 Object.values(province.districts || {}).forEach(district => {
   district.cities.forEach(city => {
     const utilized = city.total_utilization || 0; // Sum the total utilization for each city
     totalFundUtilizedPerProvince += utilized; // Add to the total fund utilized for the province
   });
 });

 // Add "TOTAL FUND UTILIZED" for the province
 provinceUtilizationSlide.addText(`TOTAL FUND UTILIZED FOR ${province.col_province.toUpperCase()}`, { 
   x: '-5%', y: '42%', w: '90%', fontSize: 23, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
 });

 // Add the calculated total fund utilized for the province
 provinceUtilizationSlide.addText(`₱${totalFundUtilizedPerProvince.toLocaleString()}`, { 
   x: '-10%', y: '52%', w: '90%', fontSize: 50, bold: true, color: '0000FF', fontFace: 'Arial', align: 'center'
 });


     // Initialize arrays to store total allocations, utilizations, targets, and served amounts per program
     const programAllocations = {};
     const programUtilizations = {};
     const programTargets = {};
     const programServed = {};
 
     programs.forEach(program => {
       // Slide 2: Allocation Summary for the Program
       const allocationSlide = pptx.addSlide();
       allocationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
 
       // Add Program Name Header
       allocationSlide.addText(` ${program.program_name}`, {
         x: 0.5,
         y: 0.5, w: '70%', fontSize: 24, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
       });
 // Add the program logo
 allocationSlide.addImage({
    path: `${window.location.origin}/${program.program_logo}`, // Assuming program_logo holds the image file name
    x: 0, y: 0, w: 1.0, h: 1.0 // Adjust x, y, width, and height as necessary
  });
 
       const allocationTableHeader = [
         [
           { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
           { text: 'TARGET', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 2 } }
         ],
         [
           { text: 'Municipality', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
           { text: 'Physical', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
           { text: 'Fund Allocated (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
         ]
       ];
 
       const allocationRows = [];
       let totalPhysicalTarget = 0;
       let totalFundAllocated = 0;
 
       Object.values(province.districts || {}).forEach(district => {
         if (district.district) {
           allocationRows.push([
             { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 10, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
           ]);
         }
 
         district.cities.forEach((city, index) => {
           const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
           const target = city.total_target || 0;
           const allocated = city.total_allocation || 0;
 
           totalPhysicalTarget += target;
           totalFundAllocated += allocated;
 
           // Store total allocations and targets per program
           programAllocations[program.program_name] = (programAllocations[program.program_name] || 0) + allocated;
           programTargets[program.program_name] = (programTargets[program.program_name] || 0) + target;
 
           allocationRows.push([
             { text: city.col_citymuni, options: { fontSize: 10, align: 'left', fontFace: 'Arial', fill: fillColor } },
             { text: target.toString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } },
             { text: allocated.toLocaleString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } }
           ]);
         });
       });
 
       allocationRows.push([
         { text: 'TOTAL', options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
         { text: totalPhysicalTarget.toString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
         { text: totalFundAllocated.toLocaleString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } }
       ]);
 
       allocationSlide.addTable([...allocationTableHeader, ...allocationRows], {
        x: 1.0, // Adjust x-position as needed
        y: 1.0, // Adjust y-position as needed
        w: 7,   // Reduced width of the entire table
        h: 3.5, // Height of the table
        colW: [2.5, 2, 2], // Adjust column widths: [Municipality, Physical Target, Fund Allocated]
        rowH: 0.2, // Adjust row height
        fontSize: 14, 
        border: { pt: 1, color: 'FFFFFF' }, 
        fill: { color: 'FFFFFF' } // Optional: adjust fill for table background
      });
 
       // Slide 3: Utilization Summary for the Program
       const utilizationSlide = pptx.addSlide();
       utilizationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
 
       // Add Program Name Header
       utilizationSlide.addText(` ${program.program_name}`, {
         x: 0.5,
         y: 0.5, w: '70%', fontSize: 24, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
       });
       utilizationSlide.addImage({
        path: `${window.location.origin}/${program.program_logo}`, // Assuming program_logo holds the image file name
        x: 0, y: 0, w: 1.0, h: 1.0 // Adjust x, y, width, and height as necessary
      });
 
       const utilizationTableHeader = [
        [
          { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
          { text: 'ACCOMPLISHMENT', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 2 } }
        ],
        [
          { text: 'Municipality', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
          { text: 'Physical', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
          { text: 'Fund Utilized (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
        ]
      ];
 
       const utilizationRows = [];
       let totalFundUtilized = 0;
       let totalServed = 0;
 
       Object.values(province.districts || {}).forEach(district => {
         if (district.district) {
           utilizationRows.push([
             { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 10, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
           ]);
         }
 
         district.cities.forEach((city, index) => {
           const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
           const utilized = city.total_utilized || 0;
           const served = city.total_served || 0;
 
           totalFundUtilized += utilized;
           totalServed += served;
 
           // Store total utilizations and served amounts per program
           programUtilizations[program.program_name] = (programUtilizations[program.program_name] || 0) + utilized;
           programServed[program.program_name] = (programServed[program.program_name] || 0) + served;
 
           utilizationRows.push([
             { text: city.col_citymuni, options: { fontSize: 10, align: 'left', fontFace: 'Arial', fill: fillColor } },
             { text: utilized.toLocaleString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } },
             { text: served.toLocaleString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } }
           ]);
         });
       });
 
       utilizationRows.push([
         { text: 'TOTAL', options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
         { text: totalFundUtilized.toLocaleString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
         { text: totalServed.toLocaleString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } }
       ]);
 
       utilizationSlide.addTable([...utilizationTableHeader, ...utilizationRows], {
        x: 1.0, // Adjust x-position as needed
        y: 1.0, // Adjust y-position as needed
        w: 7,   // Reduced width of the entire table
        h: 3.5, // Height of the table
        colW: [2.5, 2, 2], // Adjust column widths: [Municipality, Physical Target, Fund Allocated]
        rowH: 0.1, // Adjust row height
        fontSize: 14, 
        border: { pt: 1, color: 'FFFFFF' }, 
        fill: { color: 'FFFFFF' } // Optional: adjust fill for table background
      });
     });

   // Allocation Overview Slide
   const allocationOverviewSlide = pptx.addSlide();
   allocationOverviewSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
 
   // Update title to 'SUMMARY PER PROGRAM'
   allocationOverviewSlide.addText('SUMMARY PER PROGRAM', {
     x: 0.5, y: 0.5, fontSize: 24, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
   });
 
   // Table Header for Allocation Overview Slide
   const allocationOverviewHeader = [
     { text: 'Program', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
     { text: 'Physical Target', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
     { text: 'Fund Allocated (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
   ];
 
   // Allocations Overview Rows (Apply alternating row colors and format)
   const allocationOverviewRows = Object.keys(programAllocations).map((programName, index) => {
     const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
     return [
       { text: programName, options: { fontSize: 10, align: 'left', fontFace: 'Arial', fill: fillColor } },
       { text: programTargets[programName].toString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } },
       { text: programAllocations[programName].toLocaleString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } }
     ];
   });
 
   // Add total row
   const totalTarget = Object.values(programTargets).reduce((sum, value) => sum + value, 0);
   const totalAllocated = Object.values(programAllocations).reduce((sum, value) => sum + value, 0);
   const totalRow = [
     { text: 'TOTAL', options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
     { text: totalTarget.toString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
     { text: totalAllocated.toLocaleString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } }
   ];
 
   // Add table to Allocation Overview Slide
   allocationOverviewSlide.addTable([allocationOverviewHeader, ...allocationOverviewRows, totalRow], {
    x: 1.0, y: 1.0, w: 8, h: 3.5, colW: [2.5, 2, 2], border: { pt: 0.5, color: 'FFFFFF' }, fontSize: 12, fontFace: 'Arial'
  });
 
   // Utilization Overview Slide
   const utilizationOverviewSlide = pptx.addSlide();
   utilizationOverviewSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
 
   // Update title to 'SUMMARY PER PROGRAM'
   utilizationOverviewSlide.addText('SUMMARY PER PROGRAM', {
     x: 0.5, y: 0.5, fontSize: 24, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
   });
 
   // Table Header for Utilization Overview Slide
   const utilizationOverviewHeader = [
     { text: 'Program', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
     { text: 'Physical Served', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
     { text: 'Fund Utilized (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
   ];
 
   // Utilization Overview Rows (Apply alternating row colors and format)
   const utilizationOverviewRows = Object.keys(programUtilizations).map((programName, index) => {
     const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
     return [
       { text: programName, options: { fontSize: 10, align: 'left', fontFace: 'Arial', fill: fillColor } },
       { text: programServed[programName].toString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } },
       { text: programUtilizations[programName].toLocaleString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } }
     ];
   });
 
   // Add total row
   const totalServed = Object.values(programServed).reduce((sum, value) => sum + value, 0);
   const totalUtilized = Object.values(programUtilizations).reduce((sum, value) => sum + value, 0);
   const totalUtilizationRow = [
     { text: 'TOTAL', options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
     { text: totalServed.toString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
     { text: totalUtilized.toLocaleString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } }
   ];
 
   // Add table to Utilization Overview Slide
   utilizationOverviewSlide.addTable([utilizationOverviewHeader, ...utilizationOverviewRows, totalUtilizationRow], {
    x: 1.0, y: 1.0, w: 8, h: 3.5, colW: [2.5, 2, 2], border: { pt: 0.5, color: 'FFFFFF' }, fontSize: 12, fontFace: 'Arial'
  });


});
 
   // Save the PowerPoint file
   pptx.writeFile({ fileName: 'Program Report.pptx' });
 };