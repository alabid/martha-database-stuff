# removeDuplicates.py -- Daniel and Jie 
# remove all duplicates in two .csv files
#
#
#
class Value:
    def __init__(self, line, fields):
        self.line_values = line.split("\t")
        self.len_line_values = len(self.line_values)
        self.value_dict = {}


        for i in range(len(fields)):
            if self.len_line_values-1 >= i: 
                self.value_dict[fields[i]] = self.line_values[i]
            else:
                self.value_dict[fields[i]] = ""
         

    def __str__(self):
        return self.value_dict.__str__()

def makeValue(list_of_fields, line):
    pass

def getFieldsWindTurbine(data_lines):
    for each in data_lines:
        if "Table" in each:
            return each.replace("\"", "").split("\t")

def getFieldsCurrent(first_data_line):
    if "Table" in first_data_line:
        return first_data_line.replace("\"", "").split("\t")


def printList(data_list):
    for each_line in data_list:
        print each_line

def getActData(data_list):
    i = 0
   
    for i in range(len(data_list)):
        if "Table" in data_list[i]:
            return data_list[i+1:]

def insertOnTopUniqueValue(current_values, turbine_values):
    from operator import itemgetter
    newList = current_values + turbine_values
    merged = sorted(newList, key=lambda each: each.value_dict["Table"], reverse=True)
    for first, second in merged:
        while first.value_dict == second.value_dict:
            # remove it somehow

def main():
    wind_turbine_file = open("wind_turbine_data.txt", "r")
    current_file = open("current.txt", "r")

    wind_turbine_file_data = wind_turbine_file.read().split("\r")

    wind_turbine_file_fields = getFieldsWindTurbine(wind_turbine_file_data[0:4])
    wind_turbine_file_actdata = getActData(wind_turbine_file_data)
    
    current_file_data = current_file.read().split("\r")
    current_turbine_file_fields = getFieldsCurrent(current_file_data[0])
    current_file_actdata = getActData(current_file_data)

    current_values = []
    for each in current_file_actdata:
        current_values.append(Value(each, current_turbine_file_fields))
    #sorted(current_values, key=lambda each:each.value_dict["Table"], reverse=True)

    printList(current_values)

    turbine_values = []
    for each in wind_turbine_file_actdata:
        turbine_values.append(Value(each, wind_turbine_file_fields))
    #sorted(turbine_values, key=lambda each:each.value_dict["Table"], reverse=False)

    print "\n"
    printList(turbine_values)
    merged =  insertOnTopUniqueValue(current_values, turbine_values)
    printList(merged)

    print len(merged)
    
    
if __name__ == "__main__":
    main()
"""""
2/4/11: {}
"""""
