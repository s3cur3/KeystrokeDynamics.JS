###########################################################################
# evaluation-script.R                                                     #
#                                                                         #
# Comparing Anomaly Detectors for Keystroke Biometrics                    #
# Evaluation Proceedure                                                   #
# R Script                                                                #
#                                                                         #
# by: Kevin Killourhy                                                     #
# date: May 19, 2009                                                      #
###########################################################################

sink("/dev/null", type = c("output", "message")); # don't output any messages
sink.number();
library( ROCR ); # for mahalanobisTrain

# The euclideanTrain and euclideanScore functions comprise the
# Euclidean anomaly detector.  During training, the detector takes a
# set of password-timing vectors (encoded as rows in a matrix) and
# calculates the mean vector.  This mean vector is returned as the
# detection model.  During scoring, the detector takes the detection
# model and a new set of password-timing vectors (also encoded as rows
# in a matrix) and calculates the squared Euclidean distance between
# the mean vector and each of the new password-timing vectors.  These
# scores are returned in a vector whose length is equal to the number
# of password-timing vectors in the scoring matrix.

#euclideanTrain <- function( YTrain ) {
  # Construct where each entry is a Boolean: does the mean equal the
  # mean of the columns of YTrain?
#  detection.model <- list ( mean  = colMeans( YTrain ) );
#  return( detection.model );
#}

# The mahalanobisTrain and mahalanobisScore functions comprise the
# Mahalanobis anomaly detector.  During training, the detector takes a
# set of password-timing vectors (encoded as rows in a matrix) and
# calculates the mean vector and also the inverse of the covariance
# matrix.  This vector and matrix are returned as the detection model.
# During scoring, the detector takes the detection model and a new set
# of password-timing vectors (also encoded as rows in a matrix) and
# calculates the squared Mahalanobis distance between the mean vector
# and each of the new password-timing vectors.  These scores are
# returned in a vector whose length is equal to the number of
# password-timing vectors in the scoring matrix.

mahalanobisTrain <- function( YTrain ) {
  print(YTrain)
  dmod <- list( mean  = colMeans( YTrain ),
                covInv = ginv( cov( YTrain ) ) );
  return( dmod );
}


# Read this user's timing data as a data frame
datafile <- 'r/training_data.csv';
if( ! file.exists(datafile) )
  stop( "Password data file ", datafile, " does not exist");
password.timing.df <- read.csv( datafile, header = TRUE );

# Format the timing data as a matrix, suitable for passing
# to the training function
# The relevant timing data includes everything but the repetition number,
# so we take the subset of the timing data frame with that column removed
relevant.timing.data = subset( password.timing.df,
                               select = -c( repetition ) )
YTrain <- as.matrix( relevant.timing.data );

sink();
print("Getting detection model")
detection.model <- mahalanobisTrain( YTrain );

print("Serializing detection model") # Need this text for the sake of the PHP script!
#serialized.form <- serialize(detection.model, NULL, TRUE)
serialized.form <- rawToChar( serialize(detection.model, NULL, ascii=TRUE) )

# Output the data to the standard out (PHP will capture that output)
print(serialized.form)

