import xlrd as ex
import os,re
import sys


def saveData(sheet, filename, directory):
    full_name = os.path.join(directory, filename[:filename.find(".xls")]+"_"+sheet.name+".csv")
    outFile = open(full_name,"w")
    outList = []
    for i in range(sheet.nrows):
        temp = ""
        for j in range(sheet.ncols):
            temp+=str(sheet.cell_value(i,j))+"\t"
        temp+="\n"
        outList.append(temp)
    outFile.writelines(temp)
    outFile.close()

def manipulate(sheet):
    
    pass
def main():
    if (len(sys.argv)!=1):
        print "Wrong usage"
    
    directory = sys.argv[0]
    
    for root,dirs,files in os.walk(directory):
        for filename in files:
            try:
                if re.search(".xls",filename):
                    full_name=os.path.join(root,filename)
                    book=ex.open_workbook(full_name)
                    for i in range (book.nsheets):
                        sheet = book.sheet_by_index(i)
                        saveData(sheet,filename,"~/DataStorage")
            except:
               continue



if __name__=="__main__":
    main()
