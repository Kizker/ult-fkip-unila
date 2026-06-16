import json
import os

def patch_results():
    json_path = "adaptive_omr_results.json"
    if not os.path.exists(json_path):
        print(f"Error: {json_path} does not exist.")
        return
        
    with open(json_path, "r") as f:
        data = json.load(f)
        
    # Define corrections (filename: {E1: score, E2: score})
    corrections = {
        "uji k pip aulia.pdf": {"E1": 4, "E2": 4},
        "uji k pips andhini.pdf": {"E1": 4, "E2": 4},
        "uji k pips mita.pdf": {"E1": 3, "E2": 3},
        "uji k pmipa nabila.pdf": {"E1": 4, "E2": 5},
        "uji k admin riswan.pdf": {"E1": 3, "E2": 4}
    }
    
    conclusion_patches = {
        "uji k admin anisa.pdf": "b",
        "uji k pmipa nur .pdf": "b",
        "uji k ult tri .pdf": "b",
        "uji k pmipa rizky.pdf": "a"
    }
    
    print("Patching scores in JSON...")
    for filename, scores_to_patch in corrections.items():
        if filename in data:
            print(f"\nFile: {filename}")
            old_scores = data[filename]["scores"].copy()
            
            # Apply corrections
            for k, v in scores_to_patch.items():
                data[filename]["scores"][k] = v
            
            new_scores = data[filename]["scores"]
            
            # Recompute total_score and percentage in JSON
            total = sum(new_scores.values())
            pct = total / 60.0 * 100.0
            
            data[filename]["total_score"] = total
            data[filename]["percentage"] = pct
            
            print(f"  Old Page 2: E1={old_scores.get('E1')}, E2={old_scores.get('E2')} (Total={sum(old_scores.values())}, Pct={sum(old_scores.values())/60*100:.2f}%)")
            print(f"  New Page 2: E1={new_scores.get('E1')}, E2={new_scores.get('E2')} (Total={total}, Pct={pct:.2f}%)")
        else:
            print(f"Warning: {filename} not found in JSON.")
            
    print("\nPatching conclusions in JSON...")
    for filename, new_conclusion in conclusion_patches.items():
        if filename in data:
            old_conclusion = data[filename].get("conclusion")
            data[filename]["conclusion"] = new_conclusion
            print(f"  File: {filename} | Old: {old_conclusion} -> New: {new_conclusion}")
        else:
            print(f"Warning: {filename} not found in JSON.")
            
    with open(json_path, "w") as f:
        json.dump(data, f, indent=4)
        
    print("\nSuccessfully patched adaptive_omr_results.json!")
 
if __name__ == "__main__":
    patch_results()

