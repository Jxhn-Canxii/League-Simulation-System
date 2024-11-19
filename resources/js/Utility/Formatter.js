export const eliminationFormatter = (type) => {
    switch (type) {
        case 1:
            return 'Single Elimination'
            break;
        case 2:
            return 'Single Round Robin'
            break;
        case 3:
            return 'Double Round Robin'
            break;
        default:
            return 'Invalid'
            break;
    }
    // <option value="0">Select Type</option>
    // <option value="1">Single Elimination</option>
    // <option value="2">Single Round Robin</option>
    // <option value="3">Double Round Robin</option>
};
export const roundNameFormatter = (round) => {
    if (typeof round === 'number') {
        return `Round # ${round}`;
    }

    switch (round) {
        case 'play_ins_elims_round_1':
            return 'Conference Play-ins(7th vs 8th)'
        case 'play_ins_elims_round_2':
            return 'Conference Play-ins(9th vs 10th)'
        case 'play_ins_elims':
            return 'Conference Play-ins'
        case 'play_ins_finals':
            return 'Conference Play-ins Finals'
        case 'round_of_32':
            return 'Conference Round of 16'
            break;
        case 'round_of_16':
            return 'Conference Quarterfinals'
            break;
        case 'quarter_finals':
            return 'Conference Semi-Finals'
            break;
        case 'semi_finals':
            return 'Conference Finals'
            break;
        case 'interconference_semi_finals':
            return 'The Big 4'
            break;
        case 'finals':
            return 'The Finals'
            break;
        default:
            return round;
            break;
    }
}
export const roundGridFormatter = (round,start) => {
    if(start == 32){
        switch (round) {
            case 'play_ins_elims':
                return 3;
                 break;
            case 'play_ins_finals':
                return 4;
                    break;
            case 'round_of_32':
                return 5;
                 break;
            case 'round_of_16':
               return 6;
                break;
            case 'quarter_finals':
                return 7;
                break;
            case 'semi_finals':
                return 8;
                break;
            case 'interconference_semi_finals':
                return 9;
                break;
            case 'finals':
                return 10;
                break;
            default:
                return 2;
                break;
        }
    }else{
        switch (round) {
            case 'play_ins_elims':
                return 4;
                 break;
            case 'play_ins_finals':
                return 5;
                    break;
            case 'round_of_16':
                return 6;
                 break;
            case 'quarter_finals':
                return 7;
                break;
            case 'semi_finals':
                return 8;
            case 'interconference_semi_finals':
                return 9;
                break;
            case 'finals':
                return 10;
                break;
            default:
                return 2;
                break;
        }
    }

}
export const roundStatusFormatter = (round,start) => {
    const playIns = true;
    let newRound;
    if(start == 32){
        switch (round) {
            case 'start':
                newRound = 'round_of_32';
                break;
            case 'round_of_32':
                newRound = 'round_of_16';
                break;
            case 'round_of_16':
                newRound = 'quarter_finals';
                break;
            case 'quarter_finals':
                newRound = 'semi_finals';
                break;
            case 'semi_finals':
                newRound = 'interconference_semi_finals';
                break;
            case 'interconference_semi_finals':
                newRound = 'finals';
                break;
            default:
                newRound = 'invalid';
                break;
        }
    }
    else if(start == 16 && playIns == false){
        switch (round) {
            case 'start':
                newRound = 'round_of_16';
                break;
            case 'round_of_16':
                newRound = 'quarter_finals';
                break;
            case 'quarter_finals':
                newRound = 'semi_finals';
                break;
            case 'semi_finals':
                newRound = 'interconference_semi_finals';
                break;
            case 'interconference_semi_finals':
                newRound = 'finals';
                break;
            default:
                newRound = 'invalid';
                break;
        }
    }
    else if(start == 16 && playIns == true){
        switch (round) {
            case 'start':
                newRound = 'play_ins_elims';
                break;
            case 'play_ins_elims':
                newRound = 'play_ins_finals';
                break;
            case 'play_ins_finals':
                newRound = 'round_of_16';
                break;
            case 'round_of_16':
                newRound = 'quarter_finals';
                break;
            case 'quarter_finals':
                newRound = 'semi_finals';
                break;
            case 'semi_finals':
                newRound = 'interconference_semi_finals';
                break;
            case 'interconference_semi_finals':
                newRound = 'finals';
                break;
            default:
                newRound = 'invalid';
                break;
        }
    }
    else if(start == 8){
        switch (round) {
            case 'start':
                newRound = 'quarter_finals';
                break;
            case 'quarter_finals':
                newRound = 'semi_finals';
                break;
            case 'semi_finals':
                newRound = 'interconference_semi_finals';
                break;
            case 'interconference_semi_finals':
                newRound = 'finals';
                break;
            default:
                newRound = 'invalid';
                break;
        }
    }

    return newRound;
}

export const generateRandomKey = () => {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const length = 10; // You can adjust the length of the key as needed
    let result = '';
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
};
export const moneyFormatter = (amount) => {
    // Check if amount is not a valid number
    if (isNaN(amount) || amount === null || amount === undefined) {
        return ''; // Return empty string
    }
    // Convert amount to number and format with commas for thousands separator
    return Number(amount).toLocaleString('en-US', {maximumFractionDigits: 2});
}
export const playerStatusClass = (isActive) => {
    return isActive ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800";
};

export const playerStatusText = (isActive) => {
    return isActive ? "Active" : "Waived";
};
export const playerExpStatusClass = (experience) => {
    if (parseFloat(experience) == 0) {
        return "bg-green-100 text-green-800"; // Rookie
    } else if (parseFloat(experience) == 2) {
        return "bg-red-100 text-red-800"; // Sophomore
    }
    else if (parseFloat(experience) > 2) {
        return "bg-yellow-100 text-yellow-800"; // Sophomore
    }
    else {
        return "text-gray-800"; // Veteran
    }
};

export const playerExpStatusText = (experience) => {
    if (parseFloat(experience) == 1) {
        return "Rookie";
    } else if (parseFloat(experience) == 2) {
        return "Sophomore";
    } else if (parseFloat(experience) > 2) {
        return "Veteran";
    }
    else {
        return "None";
    }
};

// Helper functions
export const roleClasses = (role) => {
    switch (role) {
        case "starter":
            return "bg-blue-100 text-blue-800";
        case "star player":
            return "bg-yellow-100 text-yellow-800";
        case "role player":
            return "bg-green-100 text-green-800";
        case "bench":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-gray-200 text-gray-800"; // Default case
    }
};
  // Function to determine badge class based on role
export const roleBadgeClass = (role) => {
    switch(role) {
      case 'star player':
        return 'bg-red-500 text-white rounded-full px-2 py-1 text-xs';
      case 'starter':
        return 'bg-blue-500 text-white rounded-full px-2 py-1 text-xs';
      case 'role player':
        return 'bg-green-500 text-white rounded-full px-2 py-1 text-xs';
      case 'bench':
        return 'bg-gray-500 text-white rounded-full px-2 py-1 text-xs';
      default:
        return 'bg-gray-300 text-gray-800 rounded-full px-2 py-1 text-xs';
    }
  };

